<?php

/**
 * @package imageoptimizer
 */

$_lang['imageoptimizer'] = 'ImageOptimizer';
$_lang['imageoptimizer.desc'] = 'WebP/AVIF conversion and responsive images';
$_lang['imageoptimizer_vuetools_required'] = 'ImageOptimizer requires VueTools 1.1.2+. Install it via Package Manager.';

$_lang['imageoptimizer.tab.dashboard'] = 'Dashboard';
$_lang['imageoptimizer.tab.queue'] = 'Queue';
$_lang['imageoptimizer.tab.settings'] = 'Settings';
$_lang['imageoptimizer.tab.server'] = 'Server';
$_lang['imageoptimizer.tab.compatibility'] = 'Compatibility';

$_lang['imageoptimizer.dashboard.title'] = 'Queue overview';
$_lang['imageoptimizer.dashboard.progress'] = 'Completed';
$_lang['imageoptimizer.dashboard.readiness'] = 'Server readiness';
$_lang['imageoptimizer.dashboard.reset_stuck_done'] = 'Reset %s stuck items';

$_lang['imageoptimizer.status.pending'] = 'Pending';
$_lang['imageoptimizer.status.processing'] = 'Processing';
$_lang['imageoptimizer.status.done'] = 'Done';
$_lang['imageoptimizer.status.failed'] = 'Failed';
$_lang['imageoptimizer.status.skipped'] = 'Skipped';

$_lang['imageoptimizer.queue.empty'] = 'Queue is empty';
$_lang['imageoptimizer.queue.empty_detail'] = 'Upload images or run a rebuild to enqueue conversion jobs.';
$_lang['imageoptimizer.queue.rebuild'] = 'Rebuild queue';
$_lang['imageoptimizer.queue.clear'] = 'Clear variants';
$_lang['imageoptimizer.queue.retry'] = 'Retry selected';
$_lang['imageoptimizer.queue.reset_stuck'] = 'Reset stuck';
$_lang['imageoptimizer.queue.process'] = 'Process queue';
$_lang['imageoptimizer.queue.process_done'] = 'Processed %s items, %s still pending';
$_lang['imageoptimizer.queue.process_time_budget'] = 'PHP time limit reached. Click Process again or use cron.';
$_lang['imageoptimizer.queue.rebuild_done'] = 'Enqueued %s items';
$_lang['imageoptimizer.queue.clear_done'] = 'Removed %s variants';
$_lang['imageoptimizer.queue.retry_done'] = 'Updated %s items';
$_lang['imageoptimizer.queue.preview_count'] = 'Will enqueue %s items';
$_lang['imageoptimizer.queue.clear_preview'] = 'Will remove %s variant files';
$_lang['imageoptimizer.queue.clear_confirm'] = 'Delete variant files and queue rows? This cannot be undone.';
$_lang['imageoptimizer.queue.path_optional'] = 'File or folder path relative to source (empty = entire source)';
$_lang['imageoptimizer.queue.source_all'] = '0 = all sources in queue';

$_lang['imageoptimizer.col.id'] = 'ID';
$_lang['imageoptimizer.col.source'] = 'Source';
$_lang['imageoptimizer.col.path'] = 'Path';
$_lang['imageoptimizer.col.format'] = 'Format';
$_lang['imageoptimizer.col.width'] = 'Width';
$_lang['imageoptimizer.col.status'] = 'Status';
$_lang['imageoptimizer.col.sizes'] = 'Sizes';
$_lang['imageoptimizer.col.error'] = 'Error';

$_lang['imageoptimizer.filter.all_statuses'] = 'All statuses';
$_lang['imageoptimizer.filter.status'] = 'Status';
$_lang['imageoptimizer.filter.search'] = 'Search';
$_lang['imageoptimizer.filter.clear'] = 'Clear filters';

$_lang['imageoptimizer.live.on'] = 'Live';
$_lang['imageoptimizer.live.off'] = 'Paused';

$_lang['imageoptimizer.save'] = 'Save';
$_lang['imageoptimizer.preview'] = 'Preview';
$_lang['imageoptimizer.run'] = 'Run';
$_lang['imageoptimizer.dry_run'] = 'Dry run only';

$_lang['imageoptimizer.settings.saved'] = 'Settings saved';
$_lang['imageoptimizer.settings.tab.general'] = 'General';
$_lang['imageoptimizer.settings.tab.formats'] = 'Formats';
$_lang['imageoptimizer.settings.tab.frontend'] = 'Frontend';
$_lang['imageoptimizer.settings.tab.processing'] = 'Processing';

$_lang['imageoptimizer.server.title'] = 'Encoder check';
$_lang['imageoptimizer.server.available'] = 'Available';
$_lang['imageoptimizer.server.missing'] = 'Missing';
$_lang['imageoptimizer.server.cron'] = 'Cron command';
$_lang['imageoptimizer.server.cron_copied'] = 'Cron command copied';

$_lang['imageoptimizer.compat.title'] = 'Installed packages';
$_lang['imageoptimizer.compat.thumb3x'] = 'Thumb3x';
$_lang['imageoptimizer.compat.pthumb'] = 'pThumb / phpThumbOf';
$_lang['imageoptimizer.compat.minishop3'] = 'MiniShop3';
$_lang['imageoptimizer.compat.vuetools'] = 'VueTools';
$_lang['imageoptimizer.compat.installed'] = 'Installed';
$_lang['imageoptimizer.compat.not_installed'] = 'Not installed';
$_lang['imageoptimizer.compat.hint'] = 'ImageOptimizer skips Thumb3x URLs via skip_src_pattern.';
$_lang['imageoptimizer.compat.hint.thumb3x'] = 'URLs containing thumb3x are skipped via skip_src_pattern (on-the-fly thumbs).';
$_lang['imageoptimizer.compat.hint.pthumb'] = 'pThumb does not conflict: ImageOptimizer variants sit next to originals.';
$_lang['imageoptimizer.compat.hint.minishop3'] = 'Storefront <picture> injection without editing product chunks.';
$_lang['imageoptimizer.compat.hint.vuetools'] = 'VueTools 1.1.2+ is required for this admin UI.';

$_lang['imageoptimizer.notify.success'] = 'Success';
$_lang['imageoptimizer.notify.error'] = 'Error';

$_lang['imageoptimizer.yes'] = 'Yes';
$_lang['imageoptimizer.no'] = 'No';

$_lang['imageoptimizer.error.media_source_forbidden'] = 'No access to the media source. Check the source ID and file_list permission.';
$_lang['imageoptimizer.error.permission_denied'] = 'Insufficient permissions for this action.';
$_lang['imageoptimizer.error.unauthorized'] = 'Manager session expired. Reload the page.';
$_lang['imageoptimizer.error.connector_missing'] = 'Connector URL is missing. Reload the manager page.';
$_lang['imageoptimizer.error.invalid_response'] = 'Connector returned invalid JSON. Reload the page or check PHP logs.';
$_lang['imageoptimizer.error.request_failed'] = 'Request failed.';
$_lang['imageoptimizer.error.invalid_status'] = 'Invalid queue status.';
$_lang['imageoptimizer.error.ids_required'] = 'Select at least one queue item.';
$_lang['imageoptimizer.error.invalid_path'] = 'Invalid file path.';
$_lang['imageoptimizer.error.worker_busy'] = 'Queue processing is already running (cron or another request).';
