<?php

declare(strict_types=1);

namespace atk4\ui\demo;

date_default_timezone_set('UTC');

$isRootProject = file_exists(__DIR__ . '/../vendor/autoload.php');
/** @var \Composer\Autoload\ClassLoader $loader */
$loader = require dirname(__DIR__, $isRootProject ? 1 : 4) . '/vendor/autoload.php';
if (!$isRootProject && !class_exists(\atk4\ui\tests\ViewTest::class)) {
    throw new \Error('Demos can be run only if atk4/ui is a root composer project or if dev files are autoloaded');
}
$loader->setPsr4('atk4\ui\demo\\', __DIR__ . '/_includes');
unset($isRootProject, $loader);

// collect coverage for HTTP tests 1/2
if (file_exists(__DIR__ . '/coverage.php') && !class_exists(\PHPUnit\Framework\TestCase::class, false)) {
    require_once __DIR__ . '/coverage.php';
    \CoverageUtil::start();
}

$app = new \atk4\ui\App([
    'call_exit' => (bool) ($_GET['APP_CALL_EXIT'] ?? true),
    'catch_exceptions' => (bool) ($_GET['APP_CATCH_EXCEPTIONS'] ?? true),
    'always_run' => (bool) ($_GET['APP_ALWAYS_RUN'] ?? true),
]);
$app->title = 'Agile UI Demo v' . $app->version;

if ($app->call_exit !== true) {
    $app->stickyGet('APP_CALL_EXIT');
}

if ($app->catch_exceptions !== true) {
    $app->stickyGet('APP_CATCH_EXCEPTIONS');
}

// collect coverage for HTTP tests 2/2
if (file_exists(__DIR__ . '/coverage.php') && !class_exists(\PHPUnit\Framework\TestCase::class, false)) {
    $app->onHook(\atk4\ui\App::HOOK_BEFORE_EXIT, function () {
        \CoverageUtil::saveData();
    });
}

try {
    require_once __DIR__ . '/init-db.php';
    $app->db = $db;
    unset($db);
} catch (\Throwable $e) {
    throw new \atk4\ui\Exception('Database error: ' . $e->getMessage());
}

[$rootUrl, $relUrl] = preg_split('~(?<=/)(?=demos(/|\?|$))|\?~s', $_SERVER['REQUEST_URI'], 3);
$demosUrl = $rootUrl . 'demos/';

if (file_exists(__DIR__ . '/../public/atkjs-ui.min.js')) {
    $app->cdn['atk'] = $rootUrl . 'public';
}

// allow custom layout override
$app->initLayout([$app->stickyGET('layout') ?? \atk4\ui\Layout\Maestro::class]);

$layout = $app->layout;
if ($layout instanceof \atk4\ui\Layout\NavigableInterface) {
    $layout->addMenuItem(['Welcome to Agile Toolkit', 'icon' => 'gift'], [$demosUrl . 'index']);

    $path = $demosUrl . 'layout/';
    $menu = $layout->addMenuGroup(['Layout', 'icon' => 'object group']);
    $layout->addMenuItem(['Layouts'], [$path . 'layouts'], $menu);
    $layout->addMenuItem(['Panel'], [$path . 'layout-panel'], $menu);

    $path = $demosUrl . 'basic/';
    $menu = $layout->addMenuGroup(['Basics', 'icon' => 'cubes']);
    $layout->addMenuItem('View', [$path . 'view'], $menu);
    $layout->addMenuItem('Button', [$path . 'button'], $menu);
    $layout->addMenuItem('Header', [$path . 'header'], $menu);
    $layout->addMenuItem('Message', [$path . 'message'], $menu);
    $layout->addMenuItem('Labels', [$path . 'label'], $menu);
    $layout->addMenuItem('Menu', [$path . 'menu'], $menu);
    $layout->addMenuItem('Breadcrumb', [$path . 'breadcrumb'], $menu);
    $layout->addMenuItem(['Columns'], [$path . 'columns'], $menu);
    $layout->addMenuItem(['Grid Layout'], [$path . 'grid-layout'], $menu);

    $path = $demosUrl . 'form/';
    $menu = $layout->addMenuGroup(['Form', 'icon' => 'edit']);
    $layout->addMenuItem('Basics and Layouting', [$path . 'form'], $menu);
    $layout->addMenuItem('Data Integration', [$path . 'form2'], $menu);
    $layout->addMenuItem(['Form Sections'], [$path . 'form-section'], $menu);
    $layout->addMenuItem('Form Multi-column layout', [$path . 'form3'], $menu);
    $layout->addMenuItem(['Integration with Columns'], [$path . 'form5'], $menu);
    $layout->addMenuItem(['HTML Layout'], [$path . 'html-layout'], $menu);
    $layout->addMenuItem(['Conditional Fields'], [$path . 'jscondform'], $menu);

    $path = $demosUrl . 'form-control/';
    $menu = $layout->addMenuGroup(['Form Controls', 'icon' => 'keyboard outline']);
    $layout->addMenuItem(['Input'], [$path . 'input2'], $menu);
    $layout->addMenuItem('Input Decoration', [$path . 'input'], $menu);
    $layout->addMenuItem(['Checkboxes'], [$path . 'checkbox'], $menu);
    $layout->addMenuItem(['Value Selectors'], [$path . 'form6'], $menu);
    $layout->addMenuItem(['Lookup'], [$path . 'lookup'], $menu);
    $layout->addMenuItem(['Lookup Dependency'], [$path . 'lookup-dep'], $menu);
    $layout->addMenuItem(['Dropdown'], [$path . 'dropdown-plus'], $menu);
    $layout->addMenuItem(['File Upload'], [$path . 'upload'], $menu);
    $layout->addMenuItem(['Multi Line'], [$path . 'multiline'], $menu);
    $layout->addMenuItem(['Tree Selector'], [$path . 'tree-item-selector'], $menu);
    $layout->addMenuItem(['Query Builder'], [$path . 'query-builder'], $menu);

    $path = $demosUrl . 'collection/';
    $menu = $layout->addMenuGroup(['Data Collection', 'icon' => 'table']);
    $layout->addMenuItem(['Actions - Integration Examples'], [$path . 'actions'], $menu);
    $layout->addMenuItem('Data table with formatted columns', [$path . 'table'], $menu);
    $layout->addMenuItem(['Advanced table examples'], [$path . 'table2'], $menu);
    $layout->addMenuItem('Table interractions', [$path . 'multitable'], $menu);
    $layout->addMenuItem(['Column Menus'], [$path . 'tablecolumnmenu'], $menu);
    $layout->addMenuItem(['Column Filters'], [$path . 'tablefilter'], $menu);
    $layout->addMenuItem('Grid - Table+Bar+Search+Paginator', [$path . 'grid'], $menu);
    $layout->addMenuItem('Crud - Full editing solution', [$path . 'crud'], $menu);
    $layout->addMenuItem(['Crud with Array Persistence'], [$path . 'crud3'], $menu);
    $layout->addMenuItem(['Lister'], [$path . 'lister-ipp'], $menu);
    $layout->addMenuItem(['Table column decorator from model'], [$path . 'tablecolumns'], $menu);
    $layout->addMenuItem(['Drag n Drop sorting'], [$path . 'jssortable'], $menu);

    $path = $demosUrl . 'interactive/';
    $menu = $layout->addMenuGroup(['Interactive', 'icon' => 'talk']);
    $layout->addMenuItem('Tabs', [$path . 'tabs'], $menu);
    $layout->addMenuItem('Card', [$path . 'card'], $menu);
    $layout->addMenuItem(['Accordion'], [$path . 'accordion'], $menu);
    $layout->addMenuItem(['Wizard'], [$path . 'wizard'], $menu);
    $layout->addMenuItem(['Virtual Page'], [$path . 'virtual'], $menu);
    $layout->addMenuItem('Modal', [$path . 'modal'], $menu);
    $layout->addMenuItem(['Loader'], [$path . 'loader'], $menu);
    $layout->addMenuItem(['Console'], [$path . 'console'], $menu);
    $layout->addMenuItem(['Dynamic scroll'], [$path . 'scroll-lister'], $menu);
    $layout->addMenuItem(['Background PHP Jobs (SSE)'], [$path . 'sse'], $menu);
    $layout->addMenuItem(['Progress Bar'], [$path . 'progress'], $menu);
    $layout->addMenuItem(['Pop-up'], [$path . 'popup'], $menu);
    $layout->addMenuItem(['Toast'], [$path . 'toast'], $menu);
    $layout->addMenuItem('Paginator', [$path . 'paginator'], $menu);

    $path = $demosUrl . 'javascript/';
    $menu = $layout->addMenuGroup(['Javascript', 'icon' => 'code']);
    $layout->addMenuItem('Events', [$path . 'js'], $menu);
    $layout->addMenuItem('Element Reloading', [$path . 'reloading'], $menu);
    $layout->addMenuItem('Vue Integration', [$path . 'vue-component'], $menu);

    $path = $demosUrl . 'others/';
    $menu = $layout->addMenuGroup(['Others', 'icon' => 'plus']);
    $layout->addMenuItem('Sticky GET', [$path . 'sticky'], $menu);
    $layout->addMenuItem('More Sticky', [$path . 'sticky2'], $menu);
    $layout->addMenuItem('Recursive Views', [$path . 'recursive'], $menu);

    // view demo source page on Github
    \atk4\ui\Button::addTo($layout->menu->addItem()->addClass('aligned right'), ['View Source', 'teal', 'icon' => 'github'])
        ->on('click', $app->jsRedirect('https://github.com/atk4/ui/blob/develop/' . $relUrl, true));
}
unset($layout, $rootUrl, $relUrl, $demosUrl, $path, $menu);
