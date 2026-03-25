<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\Subcontractor;
use App\Models\Document;

class SubcontractorDocumentSeeder extends Seeder
{
    public function run(): void
    {
        $companyA = Company::where('name', 'ABC Construction')->firstOrFail();
        $companyB = Company::where('name', 'XYZ Builders')->firstOrFail();

        $subA1 = Subcontractor::create([
            'company_id'     => $companyA->id,
            'business_name'  => 'Apex Roofing LLC',
            'contact_name'   => 'John Apex',
            'contact_email'  => 'john@apexroofing.example.com',
            'contact_phone'  => '+1-555-0101',
        ]);

        $subA2 = Subcontractor::create([
            'company_id'     => $companyA->id,
            'business_name'  => 'Delta Electrical',
            'contact_name'   => null,
            'contact_email'  => 'billing@deltaelectric.example.com',
            'contact_phone'  => null,
        ]);

        $docA1 = Document::create([
            'company_id'       => $companyA->id,
            'subcontractor_id' => $subA1->id,
            'document_type'    => 'coi',
            'status'           => 'pending_review',
            'file_url'         => 'documents/fake-coi.pdf',
            'uploaded_by'      => 'pm@abcconstruction.com',
            'insurer'          => 'Acme Insurance Co.',
            'policy_number'    => 'GL-1234567',
            'coverage_amount'  => 1000000.00,
            'expiry_date'      => now()->addYear()->format('Y-m-d'),
            'holder_name'      => 'Apex Roofing LLC',
        ]);

        $docA1->logEvent('uploaded', 'pm@abcconstruction.com');
        $docA1->logEvent('extracted', 'system', ['model' => 'gpt-4o-mini']);

        $docA2 = Document::create([
            'company_id'       => $companyA->id,
            'subcontractor_id' => $subA2->id,
            'document_type'    => 'w9',
            'status'           => 'valid',
            'file_url'         => 'documents/fake-w9.pdf',
            'uploaded_by'      => 'admin@abcconstruction.com',
            'expiry_date'      => null,
        ]);

        $docA2->logEvent('uploaded', 'admin@abcconstruction.com');
        $docA2->logEvent('validated', 'admin@abcconstruction.com');

        Subcontractor::create([
            'company_id'     => $companyB->id,
            'business_name'  => 'Zephyr Plumbing',
            'contact_email'  => 'zephyr@example.com',
        ]);

        $this->command->info("Subcontractor and Document Seeded Successfully.");
    }
}
