<?php return array(
    'root' => array(
        'name' => 's3/nrv',
        'pretty_version' => 'dev-main',
        'version' => 'dev-main',
        'reference' => '9afa3177b36e603629f21a155b35a297136cb349',
        'type' => 'project',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'dev' => true,
    ),
    'versions' => array(
        's3/nrv' => array(
            'pretty_version' => 'dev-main',
            'version' => 'dev-main',
            'reference' => '9afa3177b36e603629f21a155b35a297136cb349',
            'type' => 'project',
            'install_path' => __DIR__ . '/../../',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'twbs/bootstrap' => array(
            'pretty_version' => 'v5.3.3',
            'version' => '5.3.3.0',
            'reference' => '6e1f75f420f68e1d52733b8e407fc7c3766c9dba',
            'type' => 'library',
            'install_path' => __DIR__ . '/../twbs/bootstrap',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'twitter/bootstrap' => array(
            'dev_requirement' => false,
            'replaced' => array(
                0 => 'v5.3.3',
            ),
        ),
    ),
);
