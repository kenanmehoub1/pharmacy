<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // إعادة تعيين الذاكرة المؤقتة
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ===== الصلاحيات =====
        $permissions = [
            // الأدوية
            'medicines.view',
            'medicines.create',
            'medicines.edit',
            'medicines.delete',

            // المبيعات
            'sales.view',
            'sales.create',
            'sales.edit',
            'sales.delete',

            // المشتريات
            'purchases.view',
            'purchases.create',
            'purchases.edit',
            'purchases.delete',

            // العملاء
            'customers.view',
            'customers.create',
            'customers.edit',
            'customers.delete',

            // الموردين
            'suppliers.view',
            'suppliers.create',
            'suppliers.edit',
            'suppliers.delete',

            // الوصفات
            'prescriptions.view',
            'prescriptions.create',
            'prescriptions.edit',
            'prescriptions.delete',

            // المخزون
            'inventory.view',

            // التقارير
            'reports.view',

            // الإعدادات
            'settings.manage',

            // المستخدمين
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        // ===== الأدوار =====
        $admin = Role::findOrCreate('admin', 'web');
        $pharmacist = Role::findOrCreate('pharmacist', 'web');
        $cashier = Role::findOrCreate('cashier', 'web');

        // ===== صلاحيات المدير (كل شيء) =====
        $admin->syncPermissions(Permission::all());

        // ===== صلاحيات الصيدلاني =====
        $pharmacist->syncPermissions([
            'medicines.view', 'medicines.create', 'medicines.edit',
            'sales.view', 'sales.create',
            'purchases.view', 'purchases.create',
            'customers.view', 'customers.create', 'customers.edit',
            'suppliers.view',
            'prescriptions.view', 'prescriptions.create', 'prescriptions.edit',
            'inventory.view',
            'reports.view',
        ]);

        // ===== صلاحيات الكاشير =====
        $cashier->syncPermissions([
            'medicines.view',
            'sales.view', 'sales.create',
            'customers.view', 'customers.create',
            'inventory.view',
        ]);

        $this->command->info('✅ Roles and permissions created successfully!');
    }
}