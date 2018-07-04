<?php

/**
 * The appinfo/app.php is the first file that is loaded and executed. It usually contains the application’s core configuration settings.
 *  These can include:
 *      id: This is the string under which your app will be referenced in ownCloud.
 *      order: Indicates the order in which your application will appear in the apps menu.
 *      href: The application’s default route, rendered when the application’s first loaded.
 *      icon: The application’s icon.
 *      name: The application’s title used in ownCloud.
 */

 // OC is a global namespace, without using a 'use' keyword, a global namespace can be referred by denoting \namespace, i.e., \OC in this case.
 // if using 'use' keyword is intended, the following can be rewritten as:
    // use \OC as OC
    // OC::$server->...
\OC::$server->getNavigationManager()->add(function () {
    $urlGenerator = \OC::$server->getURLGenerator();
    return [
        // The string under which your app will be referenced in owncloud
        'id' => 'ownnotes',

        // The sorting weight for the navigation.
        // The higher the number, the higher will it be listed in the navigation
        'order' => 10,

        // The route that will be shown on startup
        'href' => $urlGenerator->linkToRoute('ownnotes.page.index'),

        // The icon that will be shown in the navigation, located in img/
        'icon' => $urlGenerator->imagePath('ownnotes', 'ownnotes.svg'),

        // The application's title, used in the navigation & the settings page of your app
        'name' => \OC::$server->getL10N('ownnotes')->t('Test App'),
    ];
});