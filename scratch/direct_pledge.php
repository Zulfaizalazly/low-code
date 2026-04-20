<?php

use App\Models\User;
use App\Domain\Customer\Commands\RegisterCustomer;
use App\Domain\Facility\Commands\CreateFacility;
use Illuminate\Support\Facades\Auth;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$staff = User::where('email', 'staff@arrahnu.com')->first();
Auth::login($staff);

echo "Registering Customer...\n";
$customerAction = app(\App\Domain\Customer\Handlers\RegisterCustomerHandler::class);
$customer = $customerAction->handle(new RegisterCustomer(
    name: 'Tuan Haji Live',
    icNumber: '880808-08-8888',
    email: 'haji@live.com'
));

echo "Creating Facility...\n";
$facilityAction = app(\App\Domain\Facility\Handlers\CreateFacilityHandler::class);
$facility = $facilityAction->handle(new CreateFacility(
    customerId: $customer->id,
    productCode: 'GOLD_STANDARD',
    branchId: $staff->branch_id,
    entityId: $staff->entity_id,
    principalAmount: 15000,
    items: [
        ['item_type' => 'Gelang Gajah', 'weight_grams' => 25.5, 'purity' => 916]
    ]
));

echo "Live Test Execution Successful!\n";
echo "Customer ID: {$customer->id}\n";
echo "Facility ID: {$facility->id}\n";
echo "Reference: {$facility->facility_number}\n";
