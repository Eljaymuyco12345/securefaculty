<?php
$search = $search ?? '';
$statusFilter = $statusFilter ?? 'all';
$users = $users ?? [];
$totalUsers = $totalUsers ?? count($users);
$activeUsers = $activeUsers ?? count($users);
$deanCount = $deanCount ?? 0;
$inactiveUsers = $inactiveUsers ?? 0;
$activeNav = $activeNav ?? 'users';
$pageTitle = $pageTitle ?? 'User & Access Management - RBAC Administration';
require_once __DIR__ . '/../layouts/layout_start.php';
?>

<!-- Title Banner -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200 dark:border-slate-800 pb-3">
    <div>
        <h1 class="text-lg font-extrabold text-slate-900 dark:text-white tracking-tight">User & Access Management</h1>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
            Role-Based Access Control (RBAC) &bull; Account Provisioning &bull; RA 10173 Identity Governance
        </p>
    </div>
    <div class="flex items-center gap-2">
        <button type="button" onclick="openCreateUserModal()"
                class="px-3.5 py-1.5 bg-[#1da055] hover:bg-[#188c49] text-white rounded-lg font-bold text-xs shadow-sm transition-all flex items-center gap-1.5 cursor-pointer">
            <i class="fa-solid fa-user-plus text-xs"></i>
            <span>Provision New User</span>
        </button>
    </div>
</div>

<!-- Telemetry Summary Cards -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
    <div class="bg-white dark:bg-[#111c36] rounded-xl border border-slate-200 dark:border-slate-800 p-3.5 shadow-sm transition-colors">
        <div class="flex items-center justify-between text-slate-400 dark:text-slate-500">
            <span class="text-[10px] font-bold uppercase tracking-wider">Total Accounts</span>
            <i class="fa-solid fa-users text-blue-600 dark:text-blue-400"></i>
        </div>
        <p class="text-xl font-black text-slate-900 dark:text-slate-100 mt-1"><?= $totalUsers ?></p>
        <span class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5 block">Managed in CICI Registry</span>
    </div>

    <div class="bg-white dark:bg-[#111c36] rounded-xl border border-slate-200 dark:border-slate-800 p-3.5 shadow-sm transition-colors">
        <div class="flex items-center justify-between text-slate-400 dark:text-slate-500">
            <span class="text-[10px] font-bold uppercase tracking-wider">Active Faculty</span>
            <i class="fa-solid fa-user-check text-emerald-600 dark:text-emerald-400"></i>
        </div>
        <p class="text-xl font-black text-emerald-700 dark:text-emerald-400 mt-1"><?= $activeUsers ?></p>
        <span class="text-[10px] text-emerald-600 dark:text-emerald-400 mt-0.5 block">Authorized for Ingestion</span>
    </div>

    <div class="bg-white dark:bg-[#111c36] rounded-xl border border-slate-200 dark:border-slate-800 p-3.5 shadow-sm transition-colors">
        <div class="flex items-center justify-between text-slate-400 dark:text-slate-500">
            <span class="text-[10px] font-bold uppercase tracking-wider">Administrators</span>
            <i class="fa-solid fa-user-shield text-indigo-600 dark:text-indigo-400"></i>
        </div>
        <p class="text-xl font-black text-indigo-700 dark:text-indigo-400 mt-1"><?= $deanCount ?></p>
        <span class="text-[10px] text-indigo-600 dark:text-indigo-400 mt-0.5 block">Dean Clearance Officers</span>
    </div>

    <div class="bg-white dark:bg-[#111c36] rounded-xl border border-slate-200 dark:border-slate-800 p-3.5 shadow-sm transition-colors">
        <div class="flex items-center justify-between text-slate-400 dark:text-slate-500">
            <span class="text-[10px] font-bold uppercase tracking-wider">Suspended</span>
            <i class="fa-solid fa-user-slash text-rose-500 dark:text-rose-400"></i>
        </div>
        <p class="text-xl font-black text-rose-600 dark:text-rose-400 mt-1"><?= $inactiveUsers ?></p>
        <span class="text-[10px] text-rose-500 dark:text-rose-400 mt-0.5 block">Access Blocked Server-Side</span>
    </div>
</div>

<!-- Search & Filtering Toolbar -->
<div class="bg-white dark:bg-[#111c36] rounded-xl border border-slate-200 dark:border-slate-800 p-3 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-3 text-xs transition-colors">
    <form method="GET" action="users.php" class="flex-1 flex flex-wrap items-center gap-2">
        <div class="relative flex-1 min-w-[200px]">
            <i class="fa-solid fa-magnifying-glass text-slate-400 text-xs absolute left-3 top-2.5"></i>
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
                   placeholder="Search by name, CICI ID, email, or employee number..."
                   class="w-full pl-8 pr-3 py-1.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-800 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:bg-white dark:focus:bg-slate-900 transition-all">
        </div>

        <select name="status" onchange="this.form.submit()"
                class="px-3 py-1.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-700 dark:text-slate-200 focus:outline-none focus:border-blue-500">
            <option value="all" <?= $statusFilter === 'all' ? 'selected' : '' ?>>All Statuses</option>
            <option value="active" <?= $statusFilter === 'active' ? 'selected' : '' ?>>Active Only</option>
            <option value="inactive" <?= $statusFilter === 'inactive' ? 'selected' : '' ?>>Suspended Only</option>
        </select>

        <button type="submit" class="px-3 py-1.5 bg-[#0d2342] dark:bg-slate-800 text-white rounded-lg font-bold text-xs hover:bg-[#14335e] dark:hover:bg-slate-700 border border-transparent dark:border-slate-700 transition-all cursor-pointer">
            Filter
        </button>

        <?php if ($search !== '' || $statusFilter !== 'all'): ?>
            <a href="users.php" class="text-rose-600 dark:text-rose-400 hover:underline text-xs flex items-center gap-1">
                <i class="fa-solid fa-xmark"></i> Clear
            </a>
        <?php endif; ?>
    </form>

    <div class="text-[11px] text-slate-500 dark:text-slate-400 font-mono">
        Showing <strong><?= count($users) ?></strong> Registered Accounts
    </div>
</div>

<!-- Users Table -->
<div class="bg-white dark:bg-[#111c36] rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden transition-colors">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs border-collapse">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-900/90 text-slate-700 dark:text-slate-300 font-bold border-b border-slate-200 dark:border-slate-800 text-[11px] uppercase tracking-wider">
                    <th class="p-3">User & Profile</th>
                    <th class="p-3">Role</th>
                    <th class="p-3">Rank & Department</th>
                    <th class="p-3">Employee ID</th>
                    <th class="p-3 text-center">Archived Assets</th>
                    <th class="p-3 text-center">Status</th>
                    <th class="p-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody id="users-table-body" class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="7" class="p-12 text-center text-slate-400 dark:text-slate-500">
                            <i class="fa-solid fa-user-xmark text-3xl mb-2 block"></i>
                            No users found matching your search criteria.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $u):
                        $isActive = !isset($u['is_active']) || (int)$u['is_active'] === 1;
                        $isSelf = (int)$u['id'] === (int)$currentUser['id'];
                    ?>
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/50 transition-colors <?= !$isActive ? 'bg-rose-50/30 dark:bg-rose-950/20' : '' ?>">
                            <td class="p-3">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-full <?= $u['role'] === 'dean' ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/60 dark:text-indigo-300' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/60 dark:text-emerald-300' ?> flex items-center justify-center font-bold text-xs flex-shrink-0 shadow-inner">
                                        <?= htmlspecialchars(mb_strtoupper(mb_substr($u['full_name'] ?? 'U', 0, 1, 'UTF-8')), ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                    <div>
                                        <strong class="text-slate-900 dark:text-slate-100 block"><?= htmlspecialchars($u['full_name']) ?></strong>
                                        <span class="text-[11px] text-slate-500 dark:text-slate-400 font-mono">@<?= htmlspecialchars($u['username']) ?> &bull; <?= htmlspecialchars($u['email'] ?? 'No email') ?></span>
                                    </div>
                                </div>
                            </td>

                            <td class="p-3">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold uppercase <?= $u['role'] === 'dean' ? 'bg-indigo-50 text-indigo-700 border border-indigo-200 dark:bg-indigo-950/60 dark:text-indigo-300 dark:border-indigo-800' : 'bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-950/60 dark:text-blue-300 dark:border-blue-800' ?>">
                                    <i class="fa-solid <?= $u['role'] === 'dean' ? 'fa-shield-halved' : 'fa-user' ?> text-[9px]"></i>
                                    <span><?= strtoupper($u['role']) ?></span>
                                </span>
                            </td>

                            <td class="p-3">
                                <span class="text-slate-800 dark:text-slate-200 font-medium block"><?= htmlspecialchars($u['academic_rank'] ?? 'Faculty') ?></span>
                                <span class="text-[10px] text-slate-400 dark:text-slate-500"><?= htmlspecialchars($u['department'] ?? 'CICI') ?></span>
                            </td>

                            <td class="p-3 font-mono text-[11px] text-slate-600 dark:text-slate-400">
                                <?= htmlspecialchars($u['employee_id'] ?? 'N/A') ?>
                            </td>

                            <td class="p-3 text-center">
                                <span class="px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold font-mono text-[10px] border border-slate-200 dark:border-slate-700">
                                    <?= (int)($u['cert_count'] ?? $u['certificate_count'] ?? 0) ?> records
                                </span>
                            </td>

                            <td class="p-3 text-center">
                                <?php if ($isActive): ?>
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Suspended
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td class="p-3 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <button type="button" onclick="openEditUserModal(<?= htmlspecialchars(json_encode($u), ENT_QUOTES, 'UTF-8') ?>)"
                                            class="p-1.5 rounded bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs transition-all cursor-pointer"
                                            title="Edit Profile">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>

                                    <button type="button" onclick="openResetPasswordModal(<?= (int)$u['id'] ?>, <?= htmlspecialchars(json_encode($u['full_name']), ENT_QUOTES, 'UTF-8') ?>)"
                                            class="p-1.5 rounded bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-amber-700 dark:text-amber-400 text-xs transition-all cursor-pointer"
                                            title="Reset Security Password">
                                        <i class="fa-solid fa-key"></i>
                                    </button>

                                    <?php if (!$isSelf): ?>
                                        <button type="button" onclick="toggleUserStatus(<?= (int)$u['id'] ?>, <?= htmlspecialchars(json_encode($u['full_name']), ENT_QUOTES, 'UTF-8') ?>, <?= $isActive ? 'true' : 'false' ?>)"
                                                class="p-1.5 rounded <?= $isActive ? 'hover:bg-rose-100 dark:hover:bg-rose-950/60 text-slate-400 hover:text-rose-600 dark:hover:text-rose-400' : 'hover:bg-emerald-100 dark:hover:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400' ?> text-xs transition-all cursor-pointer"
                                                title="<?= $isActive ? 'Suspend User Access' : 'Reactivate User Access' ?>">
                                            <i class="fa-solid <?= $isActive ? 'fa-user-slash' : 'fa-user-check' ?>"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal 1: Provision New User -->
<div id="modal-create-user" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-lg w-full p-6 space-y-4 animate-in fade-in zoom-in-95">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-user-plus text-emerald-600 text-sm"></i>
                <h3 class="text-sm font-bold text-slate-900">Provision New Academic Account</h3>
            </div>
            <button type="button" onclick="closeCreateUserModal()" class="text-slate-400 hover:text-slate-700">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form id="createUserForm" onsubmit="submitCreateUser(event)" class="space-y-3 text-xs">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Username (CICI ID): *</label>
                    <input type="text" id="new_username" required placeholder="e.g., juan.delacruz"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Temporary Password: *</label>
                    <input type="password" id="new_password" required minlength="6" placeholder="Min. 6 characters"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-blue-500">
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Full Name (with Honorifics): *</label>
                <input type="text" id="new_fullname" required placeholder="e.g., Prof. Juan D. Dela Cruz, MIT"
                       class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-blue-500">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Official Email:</label>
                    <input type="email" id="new_email" placeholder="juan.delacruz@isufst.edu.ph"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Role Clearance: *</label>
                    <select id="new_role" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-blue-500 font-medium">
                        <option value="faculty" selected>Faculty (Standard Vault & Pipeline Access)</option>
                        <option value="dean">Dean (Administrative Oversight & Audits)</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Employee ID Number:</label>
                    <input type="text" id="new_empid" placeholder="e.g., ISUFST-2026-042"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Academic Rank:</label>
                    <input type="text" id="new_rank" placeholder="e.g., Assistant Professor I"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-blue-500">
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Specialization / Discipline:</label>
                <input type="text" id="new_spec" placeholder="e.g., Cybersecurity, Data Science, Software Engineering"
                       class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-blue-500">
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeCreateUserModal()" class="px-4 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-[#1da055] hover:bg-[#188c49] text-white font-bold shadow-md shadow-emerald-600/20">
                    Create Account
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 2: Edit Profile -->
<div id="modal-edit-user" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-lg w-full p-6 space-y-4 animate-in fade-in zoom-in-95">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-user-pen text-blue-600 text-sm"></i>
                <h3 class="text-sm font-bold text-slate-900">Update User Record</h3>
            </div>
            <button type="button" onclick="closeEditUserModal()" class="text-slate-400 hover:text-slate-700">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form id="editUserForm" onsubmit="submitEditUser(event)" class="space-y-3 text-xs">
            <input type="hidden" id="edit_user_id">

            <div>
                <label class="block font-bold text-slate-700 mb-1">Full Name: *</label>
                <input type="text" id="edit_fullname" required
                       class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-blue-500">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Official Email:</label>
                    <input type="email" id="edit_email"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Role Clearance: *</label>
                    <select id="edit_role" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-blue-500 font-medium">
                        <option value="faculty">Faculty</option>
                        <option value="dean">Dean</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Employee ID:</label>
                    <input type="text" id="edit_empid"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Academic Rank:</label>
                    <input type="text" id="edit_rank"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-blue-500">
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Specialization:</label>
                <input type="text" id="edit_spec"
                       class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-blue-500">
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeEditUserModal()" class="px-4 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-[#0d2342] hover:bg-[#14335e] text-white font-bold shadow-md">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 3: Reset Password -->
<div id="modal-reset-pass" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-sm w-full p-6 space-y-4 animate-in fade-in zoom-in-95">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-key text-amber-600 text-sm"></i>
                <h3 class="text-sm font-bold text-slate-900">Reset User Password</h3>
            </div>
            <button type="button" onclick="closeResetPasswordModal()" class="text-slate-400 hover:text-slate-700">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form id="resetPassForm" onsubmit="submitResetPassword(event)" class="space-y-3 text-xs">
            <input type="hidden" id="reset_user_id">
            <p class="text-slate-600 text-xs">
                Resetting credentials for: <strong id="reset_user_name" class="text-slate-900"></strong>
            </p>

            <div>
                <label class="block font-bold text-slate-700 mb-1">New Password (Bcrypt Hashed): *</label>
                <input type="password" id="reset_new_password" required minlength="6" placeholder="Min. 6 characters"
                       class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-blue-500">
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeResetPasswordModal()" class="px-4 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-amber-600 hover:bg-amber-500 text-white font-bold shadow-md shadow-amber-600/20">
                    Set New Password
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openCreateUserModal() {
        document.getElementById('modal-create-user').classList.remove('hidden');
    }
    function closeCreateUserModal() {
        document.getElementById('modal-create-user').classList.add('hidden');
        document.getElementById('createUserForm').reset();
    }

    function openEditUserModal(user) {
        document.getElementById('edit_user_id').value = user.id;
        document.getElementById('edit_fullname').value = user.full_name || '';
        document.getElementById('edit_email').value = user.email || '';
        const roleSelect = document.getElementById('edit_role');
        roleSelect.value = user.role || 'faculty';
        if (parseInt(user.id) === <?= (int)$currentUser['id'] ?>) {
            roleSelect.disabled = true;
            roleSelect.title = 'Self-demotion is restricted to protect administrative access';
        } else {
            roleSelect.disabled = false;
            roleSelect.title = '';
        }
        document.getElementById('edit_empid').value = user.employee_id || '';
        document.getElementById('edit_rank').value = user.academic_rank || '';
        document.getElementById('edit_spec').value = user.specialization || '';
        document.getElementById('modal-edit-user').classList.remove('hidden');
    }
    function closeEditUserModal() {
        document.getElementById('modal-edit-user').classList.add('hidden');
    }

    function openResetPasswordModal(id, name) {
        document.getElementById('reset_user_id').value = id;
        document.getElementById('reset_user_name').textContent = name;
        document.getElementById('reset_new_password').value = '';
        document.getElementById('modal-reset-pass').classList.remove('hidden');
    }
    function closeResetPasswordModal() {
        document.getElementById('modal-reset-pass').classList.add('hidden');
    }

    function submitCreateUser(e) {
        e.preventDefault();
        const payload = {
            action: 'create',
            username: document.getElementById('new_username').value,
            password: document.getElementById('new_password').value,
            full_name: document.getElementById('new_fullname').value,
            email: document.getElementById('new_email').value,
            role: document.getElementById('new_role').value,
            employee_id: document.getElementById('new_empid').value,
            academic_rank: document.getElementById('new_rank').value,
            specialization: document.getElementById('new_spec').value,
            csrf_token: window.CSRF_TOKEN || ''
        };

        fetch('api/user_handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': window.CSRF_TOKEN || ''
            },
            body: JSON.stringify(payload)
        })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'success') {
                closeCreateUserModal();
                showSystemAlert({
                    title: 'Account Provisioned',
                    message: data.message,
                    type: 'success',
                    onOk: () => location.reload()
                });
            } else {
                showSystemAlert({
                    title: 'Provisioning Failed',
                    message: data.message,
                    type: 'error'
                });
            }
        })
        .catch(err => {
            showSystemAlert({ title: 'Error', message: err.message, type: 'error' });
        });
    }

    function submitEditUser(e) {
        e.preventDefault();
        const payload = {
            action: 'update_profile',
            user_id: document.getElementById('edit_user_id').value,
            full_name: document.getElementById('edit_fullname').value,
            email: document.getElementById('edit_email').value,
            role: document.getElementById('edit_role').value,
            employee_id: document.getElementById('edit_empid').value,
            academic_rank: document.getElementById('edit_rank').value,
            specialization: document.getElementById('edit_spec').value,
            csrf_token: window.CSRF_TOKEN || ''
        };

        fetch('api/user_handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': window.CSRF_TOKEN || ''
            },
            body: JSON.stringify(payload)
        })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'success') {
                closeEditUserModal();
                showSystemAlert({
                    title: 'Profile Updated',
                    message: data.message,
                    type: 'success',
                    onOk: () => location.reload()
                });
            } else {
                showSystemAlert({
                    title: 'Update Failed',
                    message: data.message,
                    type: 'error'
                });
            }
        })
        .catch(err => {
            showSystemAlert({ title: 'Error', message: err.message, type: 'error' });
        });
    }

    function submitResetPassword(e) {
        e.preventDefault();
        const payload = {
            action: 'reset_password',
            user_id: document.getElementById('reset_user_id').value,
            new_password: document.getElementById('reset_new_password').value,
            csrf_token: window.CSRF_TOKEN || ''
        };

        fetch('api/user_handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': window.CSRF_TOKEN || ''
            },
            body: JSON.stringify(payload)
        })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'success') {
                closeResetPasswordModal();
                showSystemAlert({
                    title: 'Password Updated',
                    message: data.message,
                    type: 'success'
                });
            } else {
                showSystemAlert({
                    title: 'Reset Failed',
                    message: data.message,
                    type: 'error'
                });
            }
        })
        .catch(err => {
            showSystemAlert({ title: 'Error', message: err.message, type: 'error' });
        });
    }

    function toggleUserStatus(userId, fullName, currentlyActive) {
        const actionWord = currentlyActive ? 'suspend' : 'reactivate';
        showSystemConfirm({
            title: `Confirm Account ${currentlyActive ? 'Suspension' : 'Reactivation'}`,
            message: `Are you sure you wish to ${actionWord} the account for "${fullName}"? ${currentlyActive ? 'This will immediately revoke their ability to sign in or ingest records.' : 'This will restore their access to the personal vault.'}`,
            type: currentlyActive ? 'danger' : 'info',
            confirmText: `Yes, ${actionWord.toUpperCase()}`,
            onConfirm: function() {
                fetch('api/user_handler.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': window.CSRF_TOKEN || ''
                    },
                    body: JSON.stringify({
                        action: 'toggle_status',
                        user_id: userId,
                        csrf_token: window.CSRF_TOKEN || ''
                    })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.status === 'success') {
                        showSystemAlert({
                            title: 'Status Updated',
                            message: data.message,
                            type: 'success',
                            onOk: () => location.reload()
                        });
                    } else {
                        showSystemAlert({ title: 'Error', message: data.message, type: 'error' });
                    }
                })
                .catch(err => {
                    showSystemAlert({ title: 'Error', message: err.message, type: 'error' });
                });
            }
        });
    }
</script>

<?php require_once __DIR__ . '/../layouts/layout_end.php'; ?>
