<?php $__env->startSection('title', 'Reports'); ?>
<?php $__env->startSection('page-title', 'User Reports'); ?>

<?php $__env->startSection('content'); ?>
<div class="bg-white rounded-lg shadow">
    <!-- Filters -->
    <div class="p-6 border-b">
        <form method="GET" class="flex items-center space-x-4">
            <select name="status" class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">All Status</option>
                <option value="pending" <?php echo e(request('status') === 'pending' ? 'selected' : ''); ?>>Pending</option>
                <option value="reviewed" <?php echo e(request('status') === 'reviewed' ? 'selected' : ''); ?>>Reviewed</option>
                <option value="actioned" <?php echo e(request('status') === 'actioned' ? 'selected' : ''); ?>>Actioned</option>
            </select>

            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md">
                Filter
            </button>
        </form>
    </div>

    <!-- Reports Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reporter</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reported User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php $__empty_1 = true; $__currentLoopData = $reports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $report): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="<?php echo e($report->status === 'pending' ? 'bg-yellow-50' : ''); ?>">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900"><?php echo e($report->reporter->name); ?></div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <a href="<?php echo e(route('admin.users.show', $report->reported_user_id)); ?>" class="text-blue-600 hover:text-blue-900">
                            <?php echo e($report->reportedUser->name); ?>

                        </a>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-900 max-w-md truncate"><?php echo e($report->reason); ?></div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                            <?php echo e($report->status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                               ($report->status === 'actioned' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800')); ?>">
                            <?php echo e(ucfirst($report->status)); ?>

                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?php echo e($report->created_at->format('Y-m-d')); ?>

                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                        <?php if($report->status === 'pending'): ?>
                            <form action="<?php echo e(route('admin.reports.ban-user', $report->id)); ?>" method="POST" class="inline" onsubmit="return confirm('Ban this user?')">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="text-red-600 hover:text-red-900">Ban User</button>
                            </form>
                        <?php endif; ?>
                        <a href="<?php echo e(route('admin.reports.show', $report->id)); ?>" class="text-blue-600 hover:text-blue-900">View</a>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">No reports found</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="px-6 py-4 border-t">
        <?php echo e($reports->links()); ?>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/macbook/Desktop/Catch Me/Catch Me Dashbaord/resources/views/admin/reports/index.blade.php ENDPATH**/ ?>