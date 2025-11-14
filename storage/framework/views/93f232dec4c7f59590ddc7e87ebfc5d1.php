<?php $__env->startSection('title', 'User Details'); ?>
<?php $__env->startSection('page-title', 'User Details: ' . $user->name); ?>

<?php $__env->startSection('content'); ?>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- User Profile -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-center">
                <?php if($user->profile_image): ?>
                    <img src="<?php echo e(Storage::disk('s3')->url($user->profile_image)); ?>" alt="<?php echo e($user->name); ?>" class="w-32 h-32 rounded-full mx-auto mb-4">
                <?php else: ?>
                    <div class="w-32 h-32 rounded-full bg-gray-300 mx-auto mb-4 flex items-center justify-center">
                        <span class="text-4xl text-gray-600"><?php echo e(substr($user->name, 0, 1)); ?></span>
                    </div>
                <?php endif; ?>

                <h3 class="text-xl font-bold mb-2"><?php echo e($user->name); ?></h3>

                <div class="space-y-2 text-sm">
                    <p><strong>Email:</strong> <?php echo e($user->email ?? 'N/A'); ?></p>
                    <p><strong>Phone:</strong> <?php echo e($user->phone); ?></p>
                    <p><strong>Gender:</strong> <?php echo e(ucfirst($user->gender)); ?></p>
                    <p><strong>Language:</strong> <?php echo e(strtoupper($user->language)); ?></p>
                </div>

                <div class="mt-4 space-y-2">
                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo e($user->is_banned ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'); ?>">
                        <?php echo e($user->is_banned ? 'Banned' : 'Active'); ?>

                    </span>
                    <br>
                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo e($user->is_visible ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'); ?>">
                        <?php echo e($user->is_visible ? 'Visible' : 'Ghost Mode'); ?>

                    </span>
                </div>

                <?php if($user->bio): ?>
                    <div class="mt-4 pt-4 border-t">
                        <p class="text-sm text-gray-600"><?php echo e($user->bio); ?></p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="mt-6 pt-6 border-t space-y-2">
                <a href="<?php echo e(route('admin.users.edit', $user->id)); ?>" class="block w-full text-center bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                    Edit User
                </a>

                <?php if($user->is_banned): ?>
                    <form action="<?php echo e(route('admin.users.unban', $user->id)); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="block w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
                            Unban User
                        </button>
                    </form>
                <?php else: ?>
                    <form action="<?php echo e(route('admin.users.ban', $user->id)); ?>" method="POST" onsubmit="return confirm('Are you sure?')">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="block w-full bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded">
                            Ban User
                        </button>
                    </form>
                <?php endif; ?>

                <form action="<?php echo e(route('admin.users.destroy', $user->id)); ?>" method="POST" onsubmit="return confirm('Are you sure? This action cannot be undone!')">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('DELETE'); ?>
                    <button type="submit" class="block w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded">
                        Delete User
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- User Activity -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-lg shadow p-4">
                <p class="text-gray-500 text-sm">Likes Sent</p>
                <p class="text-2xl font-bold"><?php echo e($user->likesSent->count()); ?></p>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <p class="text-gray-500 text-sm">Likes Received</p>
                <p class="text-2xl font-bold"><?php echo e($user->likesReceived->count()); ?></p>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <p class="text-gray-500 text-sm">Messages Sent</p>
                <p class="text-2xl font-bold"><?php echo e($user->messagesSent->count()); ?></p>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <p class="text-gray-500 text-sm">Reports Made</p>
                <p class="text-2xl font-bold"><?php echo e($user->reportsMade->count()); ?></p>
            </div>
        </div>

        <!-- Location -->
        <?php if($user->latitude && $user->longitude): ?>
        <div class="bg-white rounded-lg shadow p-6">
            <h4 class="font-semibold mb-4">Last Known Location</h4>
            <p><strong>Latitude:</strong> <?php echo e($user->latitude); ?></p>
            <p><strong>Longitude:</strong> <?php echo e($user->longitude); ?></p>
            <p><strong>Last Active:</strong> <?php echo e($user->last_active_at ? $user->last_active_at->diffForHumans() : 'Never'); ?></p>
        </div>
        <?php endif; ?>

        <!-- Recent Likes -->
        <div class="bg-white rounded-lg shadow p-6">
            <h4 class="font-semibold mb-4">Recent Likes Received</h4>
            <div class="space-y-2">
                <?php $__empty_1 = true; $__currentLoopData = $user->likesReceived->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $like): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="flex items-center justify-between p-2 border rounded">
                        <div>
                            <p class="font-medium"><?php echo e($like->fromUser->name); ?></p>
                            <p class="text-sm text-gray-500"><?php echo e($like->created_at->diffForHumans()); ?></p>
                        </div>
                        <span class="px-2 py-1 text-xs rounded <?php echo e($like->status === 'accepted' ? 'bg-green-100 text-green-800' : ($like->status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800')); ?>">
                            <?php echo e(ucfirst($like->status)); ?>

                        </span>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-gray-500">No likes received yet</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Reports Against User -->
        <?php if($user->reportsAgainst->count() > 0): ?>
        <div class="bg-white rounded-lg shadow p-6">
            <h4 class="font-semibold mb-4 text-red-600">Reports Against This User</h4>
            <div class="space-y-2">
                <?php $__currentLoopData = $user->reportsAgainst; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $report): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="p-3 border border-red-200 rounded bg-red-50">
                        <p class="text-sm"><strong>Reporter:</strong> <?php echo e($report->reporter->name); ?></p>
                        <p class="text-sm"><strong>Reason:</strong> <?php echo e($report->reason); ?></p>
                        <p class="text-sm text-gray-500"><?php echo e($report->created_at->format('Y-m-d H:i')); ?></p>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/macbook/Desktop/Catch Me/Catch Me Dashbaord/resources/views/admin/users/show.blade.php ENDPATH**/ ?>