# Requirements Document

## Introduction

Ciri Preflight AI Field Helper memperkenalkan pembantu AI kontekstual di sebelah setiap label medan dalam borang pembetulan inline (Inline Fix Form) pada Simulation Preflight Check. Pengguna (Ketua Pegawai Ar-Rahnu) sering tidak memahami apa yang perlu diisi dalam medan teknikal seperti "BEARER TOKEN" atau "TEMPLATE KEY". Ciri ini menyediakan ikon kecil (ℹ️) di sebelah setiap label medan yang, apabila diklik, memaparkan popup terapung dengan penerangan ringkas yang dijana oleh AI. Pengguna juga boleh meminta penerangan lengkap dan bertanya soalan susulan dalam popup yang sama.

## Glossary

- **AI_Field_Helper**: Sistem pembantu AI yang menjana penerangan kontekstual untuk medan konfigurasi nod dalam Inline_Fix_Form.
- **Quick_Hint**: Penerangan ringkas (20–50 token, Bahasa Inggeris) yang dijana oleh AI, menerangkan tujuan medan, jenis nilai yang perlu dimasukkan, dan contoh ringkas.
- **Detailed_Explanation**: Penerangan panjang dan terperinci yang dijana oleh AI apabila pengguna memerlukan maklumat lanjut tentang sesuatu medan.
- **Follow_Up_Question**: Soalan susulan yang ditaip oleh pengguna dalam popup untuk mendapatkan penjelasan tambahan daripada AI.
- **Helper_Icon**: Ikon bulat kecil (ℹ️ style) berwarna putih/cerah yang diletakkan di sebelah kanan label medan dalam Inline_Fix_Form.
- **Helper_Popover**: Popup terapung gelap (dark glass theme) yang muncul berhampiran Helper_Icon, memaparkan respons AI.
- **Field_Hint_Endpoint**: Endpoint API Laravel baharu yang menerima konteks medan dan mengembalikan penerangan yang dijana oleh AI.
- **Hint_Cache**: Cache dalam memori (in-memory) di frontend yang menyimpan respons Quick_Hint berdasarkan gabungan `nodeType + fieldKey` untuk mengelakkan panggilan API berulang.
- **Simulation_Modal**: Komponen modal (`FlowSimulationModal.vue`) yang mengandungi fasa preflight check dan borang pembetulan inline.
- **Inline_Fix_Form**: Borang pembetulan dalam Simulation_Modal yang memaparkan medan yang hilang dikumpulkan mengikut nod.
- **AI_Service**: Infrastruktur AI sedia ada dalam aplikasi (termasuk `AIUIGenerator`, `PromptEngine`) yang menggunakan OpenAI/Claude API.
- **Config_Fields**: Definisi medan konfigurasi yang dikongsi (`useConfigFields.ts`) yang menentukan key, label, type, dan placeholder bagi setiap jenis nod.

## Requirements

### Requirement 1: Helper Icon Display

**User Story:** Sebagai Ketua Pegawai Ar-Rahnu, saya mahu melihat ikon bantuan AI di sebelah setiap label medan dalam borang pembetulan inline, supaya saya tahu bantuan tersedia untuk memahami setiap medan.

#### Acceptance Criteria

1. WHEN the Inline_Fix_Form renders a missing field, THE Simulation_Modal SHALL display a Helper_Icon immediately after the field label text.
2. THE Helper_Icon SHALL be a small circular icon (ℹ️ style) rendered in white or light color to match the dark glass theme of the Simulation_Modal.
3. THE Helper_Icon SHALL have a minimum touch target of 24×24 pixels to ensure clickability.
4. THE Helper_Icon SHALL display a cursor pointer on hover to indicate interactivity.

### Requirement 2: Quick Hint Generation (Level 1)

**User Story:** Sebagai Ketua Pegawai Ar-Rahnu, saya mahu mendapat penerangan ringkas apabila saya klik ikon bantuan, supaya saya cepat faham apa yang perlu diisi dalam medan tersebut.

#### Acceptance Criteria

1. WHEN the user clicks a Helper_Icon, THE AI_Field_Helper SHALL open a Helper_Popover anchored near the clicked icon.
2. WHILE the AI response is loading, THE Helper_Popover SHALL display a small loading spinner.
3. WHEN the AI response is received, THE Helper_Popover SHALL display the Quick_Hint text (20–50 tokens, in English) explaining the field purpose, expected value type, and a brief example.
4. THE Field_Hint_Endpoint SHALL accept a request payload containing `nodeType`, `fieldKey`, `fieldLabel`, and `mode` set to `quick`.
5. THE Field_Hint_Endpoint SHALL return a JSON response containing the AI-generated hint text.
6. IF the Field_Hint_Endpoint returns an error, THEN THE Helper_Popover SHALL display a user-friendly error message instead of the hint.

### Requirement 3: Frontend Hint Caching

**User Story:** Sebagai Ketua Pegawai Ar-Rahnu, saya mahu respons AI untuk penerangan ringkas di-cache supaya klik berulang pada ikon yang sama tidak membuat panggilan API baharu dan respons muncul serta-merta.

#### Acceptance Criteria

1. WHEN a Quick_Hint response is successfully received, THE Hint_Cache SHALL store the response keyed by the combination of `nodeType` and `fieldKey`.
2. WHEN the user clicks a Helper_Icon for a field that already has a cached Quick_Hint, THE Helper_Popover SHALL display the cached response immediately without making a new API call.
3. THE Hint_Cache SHALL be an in-memory cache (JavaScript Map or reactive object) that persists for the duration of the Simulation_Modal session.
4. WHEN the Simulation_Modal is closed and reopened, THE Hint_Cache SHALL be cleared.

### Requirement 4: Detailed Explanation (Level 2)

**User Story:** Sebagai Ketua Pegawai Ar-Rahnu, saya mahu mendapat penerangan yang lebih terperinci jika penerangan ringkas tidak mencukupi, supaya saya benar-benar faham apa yang perlu dilakukan.

#### Acceptance Criteria

1. WHEN a Quick_Hint is displayed in the Helper_Popover, THE Helper_Popover SHALL display a "Complete explanation" button at the bottom.
2. WHEN the user clicks the "Complete explanation" button, THE AI_Field_Helper SHALL call the Field_Hint_Endpoint with `mode` set to `detailed`.
3. WHILE the detailed AI response is loading, THE Helper_Popover SHALL display a loading spinner below the Quick_Hint text.
4. WHEN the detailed AI response is received, THE Helper_Popover SHALL expand to show the Detailed_Explanation below the Quick_Hint.
5. THE Detailed_Explanation response SHALL NOT be cached by the Hint_Cache.

### Requirement 5: Follow-Up Question (Level 2)

**User Story:** Sebagai Ketua Pegawai Ar-Rahnu, saya mahu dapat bertanya soalan susulan dalam popup yang sama jika saya masih tidak faham, supaya saya mendapat jawapan khusus untuk kekeliruan saya.

#### Acceptance Criteria

1. WHEN the Detailed_Explanation is displayed, THE Helper_Popover SHALL show a text input field and a "Send" button below the explanation.
2. WHEN the user types a question and clicks "Send", THE AI_Field_Helper SHALL call the Field_Hint_Endpoint with `mode` set to `detailed` and the `userQuestion` parameter containing the typed question.
3. WHILE the follow-up AI response is loading, THE Helper_Popover SHALL display a loading spinner below the input field.
4. WHEN the follow-up AI response is received, THE Helper_Popover SHALL display the response below the previous content in the same popup.
5. THE follow-up question response SHALL NOT be cached by the Hint_Cache.

### Requirement 6: Helper Popover UI and Positioning

**User Story:** Sebagai Ketua Pegawai Ar-Rahnu, saya mahu popup penerangan AI muncul dengan reka bentuk yang konsisten dengan tema modal dan diposisikan dengan betul supaya ia tidak terkeluar dari skrin.

#### Acceptance Criteria

1. THE Helper_Popover SHALL use a dark glass theme matching the Simulation_Modal (dark rgba background, subtle border, backdrop blur).
2. THE Helper_Popover SHALL be positioned below or to the right of the Helper_Icon, auto-adjusting to remain within the viewport boundaries.
3. WHEN the user clicks outside the Helper_Popover, THE Helper_Popover SHALL close.
4. WHEN the user clicks a different Helper_Icon while a Helper_Popover is open, THE AI_Field_Helper SHALL close the current popover and open a new one for the clicked icon.
5. THE Helper_Popover SHALL have a maximum width of 320 pixels to maintain readability.
6. THE Helper_Popover SHALL display text in a readable font size (minimum 12px) with appropriate line height.

### Requirement 7: Backend AI Field Hint Endpoint

**User Story:** Sebagai pembangun, saya mahu endpoint API yang khusus untuk menjana penerangan medan menggunakan infrastruktur AI sedia ada, supaya frontend boleh mendapatkan penerangan kontekstual tanpa membina perkhidmatan AI baharu.

#### Acceptance Criteria

1. THE Field_Hint_Endpoint SHALL be accessible at `POST /api/studio/ai/field-hint`.
2. THE Field_Hint_Endpoint SHALL accept a JSON request body with the fields: `nodeType` (string, required), `fieldKey` (string, required), `fieldLabel` (string, required), `mode` (string, required, one of `quick` or `detailed`), and `userQuestion` (string, optional).
3. THE Field_Hint_Endpoint SHALL validate the request payload and return a 422 status code with validation errors for invalid input.
4. THE Field_Hint_Endpoint SHALL use the existing AI service infrastructure (OpenAI/Claude API via HTTP client) to generate the hint text.
5. THE Field_Hint_Endpoint SHALL construct a system prompt that instructs the AI to respond in English with context about the Ar-Rahnu flow builder domain.
6. WHEN `mode` is `quick`, THE Field_Hint_Endpoint SHALL instruct the AI to generate a concise response of 20–50 tokens.
7. WHEN `mode` is `detailed`, THE Field_Hint_Endpoint SHALL instruct the AI to generate a comprehensive explanation.
8. WHEN `userQuestion` is provided, THE Field_Hint_Endpoint SHALL include the user question in the AI prompt for contextual follow-up.
9. THE Field_Hint_Endpoint SHALL return a JSON response with the structure `{ "hint": "..." }`.
10. IF the AI API call fails, THEN THE Field_Hint_Endpoint SHALL return a 500 status code with an error message.
11. THE Field_Hint_Endpoint SHALL enforce rate limiting of 30 requests per minute per user to prevent abuse.
12. THE Field_Hint_Endpoint SHALL require authentication (web session middleware).

### Requirement 8: AI Prompt Context for Ar-Rahnu Domain

**User Story:** Sebagai pembangun, saya mahu prompt AI mengandungi konteks domain Ar-Rahnu yang mencukupi, supaya penerangan yang dijana adalah tepat dan relevan untuk industri pajak gadai Islam.

#### Acceptance Criteria

1. THE Field_Hint_Endpoint SHALL include Ar-Rahnu domain context in the AI system prompt, covering node types (trigger, command, decision, approval, notification, document, gl_action, formula, payment_gateway, vault_action, api_request, tawarruq_calc, generate_pdf) and their purposes.
2. THE AI system prompt SHALL instruct the model to explain fields in the context of an Islamic pawnbroking (Ar-Rahnu) flow builder application.
3. THE AI system prompt SHALL instruct the model to provide practical examples relevant to the Ar-Rahnu domain (e.g., "surat_gadai_v1" for template keys, "$.facility.id" for context paths).
4. WHEN the `nodeType` is `payment_gateway` and the `fieldKey` relates to credentials, THE AI prompt SHALL include context about the specific payment provider (Billplz, Bayarcash, ToyyibPay, Stripe, Chip) to generate provider-specific guidance.

### Requirement 9: Error Handling and Resilience

**User Story:** Sebagai Ketua Pegawai Ar-Rahnu, saya mahu sistem mengendalikan ralat dengan baik supaya pengalaman saya tidak terganggu jika perkhidmatan AI tidak tersedia.

#### Acceptance Criteria

1. IF the Field_Hint_Endpoint is unreachable or returns a network error, THEN THE Helper_Popover SHALL display the message "Unable to load hint. Please try again." with a retry button.
2. IF the AI API returns a rate limit error, THEN THE Field_Hint_Endpoint SHALL return a 429 status code and THE Helper_Popover SHALL display "Too many requests. Please wait a moment."
3. IF the AI API response exceeds a 15-second timeout, THEN THE Field_Hint_Endpoint SHALL abort the request and return a 504 status code.
4. WHEN a retry button is displayed in the Helper_Popover, THE AI_Field_Helper SHALL re-attempt the API call when the user clicks the retry button.
