<?php

namespace App\Runtime\Automation\Nodes;

use App\Runtime\Automation\Contracts\NodeRunner;
use App\Runtime\Automation\ExecutionContext;
use App\Studio\Registry\FlowNode;
use Exception;

class FormulaNodeRunner implements NodeRunner
{
    /**
     * Supported operators for safe expression evaluation.
     */
    private const OPERATORS = ['+', '-', '*', '/', '%'];

    public function run(FlowNode $node, ExecutionContext $context): mixed
    {
        $config = $node->config ?? [];

        $formulaKey = $config['formula_key'] ?? null;
        $formula = $config['formula'] ?? null;
        $variables = $config['variables'] ?? [];
        $outputKey = $config['output_key'] ?? 'result';

        if ($formulaKey) {
            $registryFormula = \App\Services\FormulaRegistry::getFormula($formulaKey);
            if ($registryFormula) {
                $formula = $registryFormula['formula'] ?? $formula;
                $variables = array_merge($registryFormula['variables'] ?? [], $variables);
                $outputKey = $registryFormula['output_key'] ?? $outputKey;
            }
        }

        if (!$formula) {
            throw new Exception("FormulaNodeRunner: No formula defined for node {$node->node_key}");
        }

        // 1. Resolve all variable values from context
        $resolvedVars = [];
        foreach ($variables as $varName => $contextPath) {
            $value = $context->get($contextPath);
            if (!is_numeric($value)) {
                throw new Exception("FormulaNodeRunner: Variable '{$varName}' resolved to non-numeric value from path '{$contextPath}'");
            }
            $resolvedVars[$varName] = (float) $value;
        }

        // 2. Evaluate the formula safely
        $result = $this->evaluate($formula, $resolvedVars);

        // 3. Store the result in the execution context for downstream nodes
        $context->set("formula.{$outputKey}", $result);

        return [
            'result' => $result,
            'formula' => $formula,
            'variables' => $resolvedVars,
            'output_key' => $outputKey,
        ];
    }

    /**
     * Safely evaluate a mathematical expression with named variables.
     *
     * Supports: +, -, *, /, %, parentheses, and named variables.
     * Does NOT use eval() — uses a simple recursive descent parser.
     *
     * Examples:
     *   "principal * ltv_ratio" with {principal: 5000, ltv_ratio: 0.7} → 3500
     *   "(weight * purity_factor) * gold_price" → computed value
     *   "amount * 0.005" (ujrah 0.5% calculation)
     */
    private function evaluate(string $formula, array $variables): float
    {
        // Substitute variable names with their values
        $expression = $formula;
        
        // Sort variables by length descending to avoid partial replacement
        // e.g., 'total_amount' should be replaced before 'total'
        $varNames = array_keys($variables);
        usort($varNames, fn($a, $b) => strlen($b) - strlen($a));
        
        foreach ($varNames as $name) {
            $expression = str_replace($name, (string) $variables[$name], $expression);
        }

        // Validate: only allow numbers, operators, parentheses, dots, spaces
        $sanitized = preg_replace('/[^0-9\.\+\-\*\/\%\(\)\s]/', '', $expression);
        if ($sanitized !== $expression) {
            throw new Exception("FormulaNodeRunner: Invalid characters in expression '{$formula}'");
        }

        // Parse and evaluate using a safe tokenizer
        return $this->parseExpression(trim($sanitized));
    }

    /**
     * Recursive descent parser for arithmetic expressions.
     * Grammar:
     *   expression = term (('+' | '-') term)*
     *   term       = factor (('*' | '/' | '%') factor)*
     *   factor     = number | '(' expression ')'
     */
    private function parseExpression(string $expr): float
    {
        $pos = 0;
        $result = $this->parseTerm($expr, $pos);

        while ($pos < strlen($expr)) {
            $this->skipSpaces($expr, $pos);
            if ($pos >= strlen($expr)) break;

            $op = $expr[$pos] ?? null;
            if ($op === '+' || $op === '-') {
                $pos++;
                $right = $this->parseTerm($expr, $pos);
                $result = $op === '+' ? $result + $right : $result - $right;
            } else {
                break;
            }
        }

        return $result;
    }

    private function parseTerm(string $expr, int &$pos): float
    {
        $result = $this->parseFactor($expr, $pos);

        while ($pos < strlen($expr)) {
            $this->skipSpaces($expr, $pos);
            if ($pos >= strlen($expr)) break;

            $op = $expr[$pos] ?? null;
            if ($op === '*' || $op === '/' || $op === '%') {
                $pos++;
                $right = $this->parseFactor($expr, $pos);
                $result = match ($op) {
                    '*' => $result * $right,
                    '/' => $right != 0 ? $result / $right : throw new Exception("Division by zero"),
                    '%' => $right != 0 ? fmod($result, $right) : throw new Exception("Modulo by zero"),
                };
            } else {
                break;
            }
        }

        return $result;
    }

    private function parseFactor(string $expr, int &$pos): float
    {
        $this->skipSpaces($expr, $pos);

        // Handle negative numbers
        $negative = false;
        if ($pos < strlen($expr) && $expr[$pos] === '-') {
            $negative = true;
            $pos++;
            $this->skipSpaces($expr, $pos);
        }

        if ($pos < strlen($expr) && $expr[$pos] === '(') {
            $pos++; // skip '('
            $result = $this->parseExpression(substr($expr, $pos));
            
            // Find matching ')'
            $depth = 1;
            $innerPos = 0;
            $inner = substr($expr, $pos);
            for ($i = 0; $i < strlen($inner); $i++) {
                if ($inner[$i] === '(') $depth++;
                if ($inner[$i] === ')') $depth--;
                if ($depth === 0) {
                    $innerPos = $i;
                    break;
                }
            }

            // Re-parse the inner expression properly
            $innerExpr = substr($expr, $pos, $innerPos);
            $result = $this->parseExpression($innerExpr);
            $pos += $innerPos + 1; // skip past ')'
        } else {
            // Parse number
            $numStr = '';
            while ($pos < strlen($expr) && (is_numeric($expr[$pos]) || $expr[$pos] === '.')) {
                $numStr .= $expr[$pos];
                $pos++;
            }

            if ($numStr === '') {
                throw new Exception("FormulaNodeRunner: Expected number at position {$pos}");
            }

            $result = (float) $numStr;
        }

        return $negative ? -$result : $result;
    }

    private function skipSpaces(string $expr, int &$pos): void
    {
        while ($pos < strlen($expr) && $expr[$pos] === ' ') {
            $pos++;
        }
    }
}
