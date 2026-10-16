<?php

namespace Database\Seeders;

use App\Models\Medicine;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class MedicineSeeder extends Seeder
{
    public function run(): void
    {
        $companies = [
            'GSK', 'Pfizer', 'Novartis', 'Bayer', 'Roche',
            'Sanofi', 'AstraZeneca', 'MSD', 'Abbott', 'Julphar',
            'Amoun', 'EIPICO', 'Memphis', 'Nile', 'Alexandria',
        ];

        $categories = [
            'Tablets', 'Capsules', 'Syrup', 'Injection', 'Cream',
            'Ointment', 'Drops', 'Spray', 'Suppository', 'Sachet',
        ];

        $medicineNames = [
            'Panadol', 'Brufen', 'Augmentin', 'Congestal', 'Zyrtec',
            'Glucophage', 'Concor', 'Nexium', 'Crestor', 'Lipitor',
            'Voltaren', 'Cataflam', 'Flagyl', 'Amoxil', 'Zithromax',
            'Ventolin', 'Symbicort', 'Singulair', 'Claritin', 'Motilium',
            'Controloc', 'Antinal', 'Spasfon', 'Cipro', 'Diflucan',
            'Fucidin', 'Betadine', 'Dermovate', 'Kenacomb', 'Fusidic',
        ];

        $scientificNames = [
            'Paracetamol', 'Ibuprofen', 'Amoxicillin', 'Chlorpheniramine',
            'Cetirizine', 'Metformin', 'Bisoprolol', 'Esomeprazole',
            'Rosuvastatin', 'Atorvastatin', 'Diclofenac', 'Potassium Diclofenac',
            'Metronidazole', 'Amoxicillin', 'Azithromycin', 'Salbutamol',
            'Budesonide', 'Montelukast', 'Loratadine', 'Domperidone',
        ];

        $this->command->info('Generating 200 medicines...');

        for ($i = 1; $i <= 200; $i++) {
            $name = $medicineNames[array_rand($medicineNames)] . ' ' . rand(100, 999);
            $scientificName = $scientificNames[array_rand($scientificNames)];
            $company = $companies[array_rand($companies)];
            $category = $categories[array_rand($categories)];
            $cost = rand(5, 500);
            $price = $cost + rand(5, 100);
            $stock = rand(0, 200);
            $minStock = rand(5, 20);

            // تاريخ الصلاحية: 60% سليم، 25% ينتهي خلال 90 يوم، 15% منتهي
            $expiryRandom = rand(1, 100);
            if ($expiryRandom <= 60) {
                // سليم (بعد 90 يوم إلى 3 سنوات)
                $expiryDate = Carbon::today()->addDays(rand(91, 1095));
            } elseif ($expiryRandom <= 85) {
                // ينتهي خلال 90 يوم
                $expiryDate = Carbon::today()->addDays(rand(1, 90));
            } else {
                // منتهي
                $expiryDate = Carbon::today()->subDays(rand(1, 365));
            }

            Medicine::create([
                'name' => $name,
                'scientific_name' => $scientificName,
                'company_name' => $company,
                'barcode' => '628' . str_pad($i, 10, '0', STR_PAD_LEFT),
                'category' => $category,
                'price' => $price,
                'cost' => $cost,
                'stock' => $stock,
                'min_stock' => $minStock,
                'expiry_date' => $expiryDate,
                'description' => 'Test medicine #' . $i,
            ]);
        }

        $this->command->info('✅ 200 medicines generated successfully!');
    }
}