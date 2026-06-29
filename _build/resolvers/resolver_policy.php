<?php

/**
 * Resolver: Access Policy permissions for ImageOptimizer.
 *
 * @package imageoptimizer
 */

use MODX\Revolution\modAccessPermission;
use MODX\Revolution\modAccessPolicy;
use MODX\Revolution\modAccessPolicyTemplate;
use xPDO\Transport\xPDOTransport;

/** @var xPDOTransport $transport */
/** @var array $options */
if (!$transport->xpdo || !($transport instanceof xPDOTransport)) {
    return true;
}

$modx = $transport->xpdo;

if ($options[xPDOTransport::PACKAGE_ACTION] !== xPDOTransport::ACTION_INSTALL
    && $options[xPDOTransport::PACKAGE_ACTION] !== xPDOTransport::ACTION_UPGRADE
) {
    return true;
}

$perms = [
    'imageoptimizer_view' => 'area.imageoptimizer.view',
    'imageoptimizer_settings' => 'area.imageoptimizer.settings',
    'imageoptimizer_run' => 'area.imageoptimizer.run',
];

$templateNames = [modAccessPolicyTemplate::TEMPLATE_ADMINISTRATOR];
$managerPolicy = $modx->getObject(modAccessPolicy::class, ['name' => 'Manager']);
if ($managerPolicy) {
    $managerTemplate = $managerPolicy->getOne('Template');
    if ($managerTemplate) {
        $managerTemplateName = (string) $managerTemplate->get('name');
        if ($managerTemplateName !== '' && !in_array($managerTemplateName, $templateNames, true)) {
            $templateNames[] = $managerTemplateName;
        }
    }
}

foreach ($templateNames as $templateName) {
    $template = $modx->getObject(modAccessPolicyTemplate::class, ['name' => $templateName]);
    if (!$template) {
        $modx->log(modX::LOG_LEVEL_WARN, "[imageoptimizer] Access policy template not found: {$templateName}");
        continue;
    }
    $templateId = (int) $template->get('id');
    foreach ($perms as $permName => $description) {
        $permission = $modx->getObject(modAccessPermission::class, [
            'template' => $templateId,
            'name' => $permName,
        ]);
        if ($permission) {
            continue;
        }
        $permission = $modx->newObject(modAccessPermission::class);
        $permission->fromArray([
            'name' => $permName,
            'description' => $description,
            'template' => $templateId,
            'value' => true,
        ]);
        $permission->save();
    }
}

/** @var modAccessPolicy[] $policies */
$policies = $modx->getCollection(modAccessPolicy::class, ['name:IN' => ['Administrator', 'Manager']]);
foreach ($policies as $policy) {
    $data = $policy->get('data');
    if (is_string($data)) {
        $decoded = json_decode($data, true);
        $data = is_array($decoded) ? $decoded : (unserialize($data, ['allowed_classes' => false]) ?: []);
    }
    if (!is_array($data)) {
        $data = [];
    }
    foreach (array_keys($perms) as $permName) {
        $data[$permName] = true;
    }
    $policy->set('data', $data);
    $policy->save();
}

return true;
