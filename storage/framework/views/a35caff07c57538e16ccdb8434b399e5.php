<?php $__env->startSection('title', 'Likes & Matches'); ?>
<?php $__env->startSection('page-title', 'Likes & Matches'); ?>

<?php $__env->startSection('content'); ?>
<div class="bg-white rounded-lg shadow">
    <!-- Filters -->
    <div class="p-6 border-b">
        <form method="GET" class="flex items-center space-x-4">
            <select name="status" class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">All Status</option>
                <option value="pending" <?php echo e(request('status') === 'pending' ? 'selected' : ''); ?>>Pending</option>
                <option value="accepted" <?php echo e(request('status') === 'accepted' ? 'selected' : ''); ?>>Accepted (Matches)</option>
                <option value="rejected" <?php echo e(request('status') === 'rejected' ? 'selected' : ''); ?>>Rejected</option>
            </select>

            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md">
                Filter
            </button>
        </form>
    </div>

    <!-- Likes Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">From User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">To User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php $__empty_1 = true; $__currentLoopData = $likes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $like): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="text-sm font-medium text-gray-900"><?php echo e($like->fromUser->name); ?></div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900"><?php echo e($like->toUser->name); ?></div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                            <?php echo e($like->status === 'accepted' ? 'bg-green-100 text-green-800' :
                               ($like->status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800')); ?>">
                            <?php echo e(ucfirst($like->status)); ?>

                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?php echo e($like->created_at->format('Y-m-d H:i')); ?>

                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <form action="<?php echo e(route('admin.likes.destroy', $like->id)); ?>" method="POST" onsubmit="return confirm('Are you sure?')">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">No likes found</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="px-6 py-4 border-t">
        <?php echo e($likes->links()); ?>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/macbook/Desktop/Catch Me/Catch Me Dashbaord/resources/views/admin/likes/index.blade.php ENDPATH**/ ?>