<?php // routes/breadcrumbs.php

// Note: Laravel will automatically resolve `Breadcrumbs::` without
// this import. This is nice for IDE syntax and refactoring.π

use App\Models\Role;
use App\Models\UnitUsaha;
use Diglactic\Breadcrumbs\Breadcrumbs;

// This import is also not required, and you could replace `BreadcrumbTrail $trail`
//  with `$trail`. This is nice for IDE type checking and completion.
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;

// Home
Breadcrumbs::for('home', function (BreadcrumbTrail $trail) {
    $trail->push('Home', route('home'));
});

// User
Breadcrumbs::for('user', function (BreadcrumbTrail $trail) {
    $trail->push('User', route('user'));
});

// Permission
Breadcrumbs::for('permission', function (BreadcrumbTrail $trail) {
    $trail->push('Permission', route('permission'));
});

// Role
Breadcrumbs::for('role', function (BreadcrumbTrail $trail) {
    $trail->push('Role', route('role'));
});
Breadcrumbs::for('role.detail', function (BreadcrumbTrail $trail, $id) {
    $trail->parent('role');
    $trail->push(ucwords(Role::find($id)['name']), route('role.detail', $id));
});

Breadcrumbs::for('setting', function (BreadcrumbTrail $trail) {
    $trail->push('Setting', route('setting'));
});
Breadcrumbs::for('filemanager', function (BreadcrumbTrail $trail) {
    $trail->push('Filemanager', route('filemanager'));
});


//Audit Trace
Breadcrumbs::for('audit-trace', function (BreadcrumbTrail $trail) {
    $trail->push('Audit Trace', route('audit-trace'));
});

Breadcrumbs::for('change-password', function (BreadcrumbTrail $trail) {
    $trail->push('Change Password', route('change-password'));
});


Breadcrumbs::for('wahana', function (BreadcrumbTrail $trail) {
    $trail->push('Wahana', route('wahana'));
});

Breadcrumbs::for('ticket', function (BreadcrumbTrail $trail) {
    $trail->push('Ticket', route('ticket'));
});
Breadcrumbs::for('ticket-package', function (BreadcrumbTrail $trail) {
    $trail->push('Ticket Package', route('ticket-package'));
});

Breadcrumbs::for('ticket-package.create', function (BreadcrumbTrail $trail) {
    $trail->parent('ticket-package');
    $trail->push('Create', route('ticket-package.create'));
});

Breadcrumbs::for('ticket-package.edit', function (BreadcrumbTrail $trail, $id) {
    $trail->parent('ticket-package');
    $trail->push('Edit', route('ticket-package.edit', $id));
});


Breadcrumbs::for('transaction', function (BreadcrumbTrail $trail) {
    $trail->push('Transaction', route('transaction'));
});


Breadcrumbs::for('report.transaction', function (BreadcrumbTrail $trail) {
    $trail->push('Report', route('report.transaction'));
});
// // Home > Blog
// Breadcrumbs::for('blog', function (BreadcrumbTrail $trail) {
//     $trail->parent('home');
//     $trail->push('Blog', route('blog'));
// });

// // Home > Blog > [Category]
// Breadcrumbs::for('category', function (BreadcrumbTrail $trail, $category) {
//     $trail->parent('blog');
//     $trail->push($category->title, route('category', $category));
// });
