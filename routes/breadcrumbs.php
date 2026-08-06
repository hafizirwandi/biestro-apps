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

Breadcrumbs::for('free-gift', function (BreadcrumbTrail $trail) {
    $trail->push('Free Gift Rule', route('free-gift'));
});

Breadcrumbs::for('free-gift.create', function (BreadcrumbTrail $trail) {
    $trail->parent('free-gift');
    $trail->push('Create', route('free-gift.create'));
});

Breadcrumbs::for('free-gift.edit', function (BreadcrumbTrail $trail, $id) {
    $trail->parent('free-gift');
    $trail->push('Edit', route('free-gift.edit', $id));
});

Breadcrumbs::for('transaction', function (BreadcrumbTrail $trail) {
    $trail->push('Transaction', route('transaction'));
});

Breadcrumbs::for('report.transaction', function (BreadcrumbTrail $trail) {
    $trail->push('Report', route('report.transaction'));
});

Breadcrumbs::for('report.ticket', function (BreadcrumbTrail $trail) {
    $trail->push('Report / Ticket', route('report.ticket'));
});

Breadcrumbs::for('report.shift', function (BreadcrumbTrail $trail) {
    $trail->push('Report / Shift Cashier', route('report.shift'));
});

Breadcrumbs::for('report.items', function (BreadcrumbTrail $trail) {
    $trail->push('Report / Item Sales', route('report.items'));
});

Breadcrumbs::for('report.payment', function (BreadcrumbTrail $trail) {
    $trail->push('Report / Payment Methods', route('report.payment'));
});

Breadcrumbs::for('report.revenue', function (BreadcrumbTrail $trail) {
    $trail->push('Report / Omset', route('report.revenue'));
});

Breadcrumbs::for('report.popular-wahana', function (BreadcrumbTrail $trail) {
    $trail->push('Report / Wahana Terpopuler', route('report.popular-wahana'));
});

Breadcrumbs::for('report.ticket-usage', function (BreadcrumbTrail $trail) {
    $trail->push('Report / Penggunaan Tiket', route('report.ticket-usage'));
});

Breadcrumbs::for('playground.report', function (BreadcrumbTrail $trail) {
    $trail->push('Report / Playground', route('playground.report'));
});

Breadcrumbs::for('counter', function (BreadcrumbTrail $trail) {
    $trail->push('Counter', route('counter'));
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
