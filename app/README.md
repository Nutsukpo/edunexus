# EDUNEXUS Fresh RBAC Patch

Target stack:
- Laravel 13
- Spatie Laravel Permission 7.3
- EDUNEXUS existing architecture

This patch establishes the fresh RBAC foundation for these seven roles:

1. Super Admin
2. Administrator
3. Teaching Staff
4. Accountant
5. MIS
6. Power User
7. Non-Teaching Staff

It also includes a reusable Teaching Staff data-scope service.

IMPORTANT:
- The patch does not recover deleted RBAC data.
- The seeder creates the fresh roles and permissions.
- Super Admin starts with all permissions so the system cannot be locked out.
- The other six roles intentionally start with no permissions; assign them manually through the Role Permissions page.
- The scope service restricts Teaching Staff to assigned classes and class/subject combinations.

INSTALLATION:
1. Copy the files into the matching EDUNEXUS directories.
2. Add the routes/import from routes/rbac-routes.php into routes/web.php.
3. Run:
   php artisan db:seed --class=RolesAndPermissionsSeeder
   php artisan permission:cache-reset
   php artisan optimize:clear

Role Permissions URL:
   /roles-permissions

DATA SCOPE:
Teaching Staff access is based on the project's existing:
- student_classes.staff_id
- student_class_staff
- class_subject_staff

Academic year is inherited from student_classes.academic_year_id.
