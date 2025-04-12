<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\ParentGuardian;
use Illuminate\Database\Seeder;

class ParentGuardianSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating parent/guardian records...');

        // Get parent users
        $parentUsers = User::where('email', 'like', '%.parent@stfrancisofassisi.tech')->get();

        foreach ($parentUsers as $user) {
            $this->createParentGuardian($user);
        }

        // Also create some parents without user accounts (just records)
        for ($i = 1; $i <= 20; $i++) {
            $this->createParentGuardian();
        }

        $this->command->info('Successfully seeded ' . ParentGuardian::count() . ' parents/guardians!');
    }

    /**
     * Create a parent/guardian record
     */
    private function createParentGuardian(?User $user = null): ParentGuardian
    {
        // Generate a random parent name if no user is provided
        $name = $user ? $user->name : $this->getRandomName();

        // Generate a random Zambian phone number
        $phoneNumber = '+26097' . rand(1000000, 9999999);
        $alternatePhone = rand(0, 1) ? '+26096' . rand(1000000, 9999999) : null;

        // Generate a random Zambian NRC number (National Registration Card)
        $nrc = rand(100000, 999999) . '/' . rand(10, 99) . '/' . rand(1, 9);

        // Relationship options matching your enum
        $relationships = ['father', 'mother', 'guardian', 'other'];

        // Nationality (mostly Zambian with some others)
        $nationalities = [
            'Zambian', 'Zambian', 'Zambian', 'Zambian', 'Zambian',
            'Zambian', 'Zambian', 'Zambian', 'Zambian', 'Zambian', // 10x Zambian for higher probability
            'Zimbabwean', 'Malawian', 'South African', 'Congolese', 'Tanzanian',
            'Kenyan', 'Ugandan', 'Namibian', 'Botswanan', 'Angolan'
        ];

        // Occupation options
        $occupations = [
            'Teacher', 'Doctor', 'Nurse', 'Engineer', 'Farmer', 'Business Owner',
            'Government Employee', 'Bank Employee', 'Driver', 'Mechanic', 'Lawyer',
            'Accountant', 'Police Officer', 'Military Personnel', 'Trader', 'Miner',
            'Construction Worker', 'Tailor', 'Retired', 'Self-employed'
        ];

        // Address parts for realistic Zambian addresses
        $areas = [
            'Kabulonga', 'Woodlands', 'Chilenje', 'Matero', 'Kalingalinga', 'Chelstone',
            'Avondale', 'Roma', 'Northmead', 'Olympia', 'Sunningdale', 'Emmasdale',
            'Garden', 'Ibex Hill', 'Kabwata', 'Kamwala', 'Libala', 'Longacres',
            'Makeni', 'Chainda', 'Chawama', 'Mtendere', 'PHI'
        ];

        // Create address
        $houseNumber = rand(1, 999);
        $area = $areas[array_rand($areas)];
        $address = "House No. {$houseNumber}, {$area}, Lusaka";

        // Create the parent/guardian record
        $parent = new ParentGuardian([
            'name' => $name,
            'email' => $user ? $user->email : strtolower(str_replace(' ', '.', $name)) . '@example.com',
            'phone' => $phoneNumber,
            'nrc' => $nrc,
            'nationality' => $nationalities[array_rand($nationalities)],
            'alternate_phone' => $alternatePhone,
            'relationship' => $relationships[array_rand($relationships)],
            'occupation' => $occupations[array_rand($occupations)],
            'address' => $address,
            'user_id' => $user?->id,
        ]);

        $parent->save();
        return $parent;
    }

    /**
     * Generate a random name
     */
    private function getRandomName(): string
    {
        $firstNames = [
            'Chipo', 'Mulenga', 'Mutale', 'Bwalya', 'Chomba', 'Mwila', 'Nkonde', 'Musonda', 'Chilufya', 'Kalaba',
            'Mwamba', 'Chanda', 'Zulu', 'Tembo', 'Banda', 'Phiri', 'Mbewe', 'Lungu', 'Daka', 'Mumba',
            'Muleya', 'Ngoma', 'Ngulube', 'Sinkala', 'Ng\'andu', 'Kalumba', 'Mwansa', 'Kabwe', 'Bupe', 'Mwape'
        ];

        $lastNames = [
            'Mwila', 'Banda', 'Phiri', 'Mbewe', 'Zulu', 'Tembo', 'Chanda', 'Mutale', 'Bwalya', 'Musonda',
            'Daka', 'Mulenga', 'Mumba', 'Ngoma', 'Ngulube', 'Sinkala', 'Ng\'andu', 'Kalumba', 'Chisenga', 'Mwansa'
        ];

        return $firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)];
    }
}
