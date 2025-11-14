<?php $__env->startSection('title', 'App Settings'); ?>
<?php $__env->startSection('page-title', 'App Settings'); ?>

<?php $__env->startSection('content'); ?>
<div class="bg-white rounded-lg shadow">
    <form method="POST" action="<?php echo e(route('admin.settings.update')); ?>" class="p-6 space-y-6">
        <?php echo csrf_field(); ?>

        <!-- Distance Settings -->
        <div>
            <h3 class="text-lg font-semibold mb-4 border-b pb-2">Distance Settings</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="max_distance" class="block text-sm font-medium text-gray-700 mb-2">
                        Maximum Distance (meters)
                    </label>
                    <input type="number" id="max_distance" name="max_distance"
                           value="<?php echo e(old('max_distance', $settings['limits']['max_distance'] ?? 50)); ?>"
                           min="10" max="1000"
                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-500 mt-1">Default proximity range for finding nearby users</p>
                </div>
            </div>
        </div>

        <!-- Feature Settings -->
        <div>
            <h3 class="text-lg font-semibold mb-4 border-b pb-2">Feature Settings</h3>
            <div class="space-y-3">
                <div class="flex items-center">
                    <input type="checkbox" id="ghost_mode_enabled" name="ghost_mode_enabled" value="1"
                           <?php echo e(old('ghost_mode_enabled', $settings['features']['ghost_mode_enabled'] ?? true) ? 'checked' : ''); ?>

                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="ghost_mode_enabled" class="ml-2 block text-sm text-gray-900">
                        Enable Ghost Mode Globally
                    </label>
                </div>
                <p class="text-xs text-gray-500 ml-6">Allow users to hide themselves from the map</p>
            </div>
        </div>

        <!-- Content Settings -->
        <div>
            <h3 class="text-lg font-semibold mb-4 border-b pb-2">Legal Content</h3>

            <div class="mb-4">
                <label for="terms_content" class="block text-sm font-medium text-gray-700 mb-2">
                    Terms & Conditions
                </label>
                <textarea id="terms_content" name="terms_content" rows="6"
                          class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo e(old('terms_content', $settings['content']['terms_content'] ?? '')); ?></textarea>
            </div>

            <div>
                <label for="privacy_content" class="block text-sm font-medium text-gray-700 mb-2">
                    Privacy Policy
                </label>
                <textarea id="privacy_content" name="privacy_content" rows="6"
                          class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo e(old('privacy_content', $settings['content']['privacy_content'] ?? '')); ?></textarea>
            </div>
        </div>

        <div class="flex justify-end pt-4 border-t">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md">
                Save Settings
            </button>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/macbook/Desktop/Catch Me/Catch Me Dashbaord/resources/views/admin/settings/index.blade.php ENDPATH**/ ?>