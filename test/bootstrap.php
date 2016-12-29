<?php

//If you changed this, change it here too
$adminUser = "Administrator";
$adminPass = "admin";
$authorUser = "Author";
$authorPass = "author";
$wfsUser = "WfsUser";
$wfsPass = "wfs";
$wmsUser = "WmsUser";
$wmsPass = "wms";

$user1User = "User1";
$user1Pass = "user1";
$user2User = "User2";
$user2Pass = "user2";
$userGroup = "RestUsers";

function SetupTestData() {
    global $adminUser;
    global $adminPass;
    
    global $user1User;
    global $user1Pass;
    global $user2User;
    global $user2Pass;
    global $userGroup;
    
    $webConfigPath = dirname(__FILE__)."/../../webconfig.ini";
    MgInitializeWebTier($webConfigPath);

    $mgp = dirname(__FILE__)."/data/Sheboygan.mgp";
    if (!file_exists($mgp)) {
        echo "Please put Sheboygan.mgp into the /data directory before running this test suite";
        die;
    }

    if (!is_dir(dirname(__FILE__)."/../conf/data/test_anonymous/"))
        mkdir(dirname(__FILE__)."/../conf/data/test_anonymous/");
    copy(dirname(__FILE__)."/data/restcfg_anonymous.json", dirname(__FILE__)."/../conf/data/test_anonymous/restcfg.json");
    if (!is_dir(dirname(__FILE__)."/../conf/data/test_author/"))
        mkdir(dirname(__FILE__)."/../conf/data/test_author/");
    copy(dirname(__FILE__)."/data/restcfg_author.json", dirname(__FILE__)."/../conf/data/test_author/restcfg.json");
    if (!is_dir(dirname(__FILE__)."/../conf/data/test_administrator/"))
        mkdir(dirname(__FILE__)."/../conf/data/test_administrator/");
    copy(dirname(__FILE__)."/data/restcfg_administrator.json", dirname(__FILE__)."/../conf/data/test_administrator/restcfg.json");
    if (!is_dir(dirname(__FILE__)."/../conf/data/test_wfsuser/"))
        mkdir(dirname(__FILE__)."/../conf/data/test_wfsuser/");
    copy(dirname(__FILE__)."/data/restcfg_wfsuser.json", dirname(__FILE__)."/../conf/data/test_wfsuser/restcfg.json");
    if (!is_dir(dirname(__FILE__)."/../conf/data/test_wmsuser/"))
        mkdir(dirname(__FILE__)."/../conf/data/test_wmsuser/");
    copy(dirname(__FILE__)."/data/restcfg_wmsuser.json", dirname(__FILE__)."/../conf/data/test_wmsuser/restcfg.json");
    if (!is_dir(dirname(__FILE__)."/../conf/data/test_group/"))
        mkdir(dirname(__FILE__)."/../conf/data/test_group/");
    copy(dirname(__FILE__)."/data/restcfg_group.json", dirname(__FILE__)."/../conf/data/test_group/restcfg.json");
    if (!is_dir(dirname(__FILE__)."/../conf/data/test_mixed/"))
        mkdir(dirname(__FILE__)."/../conf/data/test_mixed/");
    copy(dirname(__FILE__)."/data/restcfg_mixed.json", dirname(__FILE__)."/../conf/data/test_mixed/restcfg.json");

    $source = new MgByteSource($mgp);
    $br = $source->GetReader();

    $siteConn = new MgSiteConnection();
    $userInfo = new MgUserInformation($adminUser, $adminPass);
    $siteConn->Open($userInfo);

    $site = new MgSite();
    $site->Open($userInfo);
    //Set up any required users
    try {
        $site->AddGroup($userGroup, "Group for mapguide-rest test suite users");
    } catch (MgException $ex) { }
    try {
        $site->AddUser($user1User, $user1User, $user1Pass, "Test user for mapguide-rest test suite");
    } catch (MgException $ex) { }
    try {
        $site->AddUser($user2User, $user2User, $user2Pass, "Test user for mapguide-rest test suite");
    } catch (MgException $ex) { }
    try {
        $groups = new MgStringCollection();
        $users = new MgStringCollection();
        $groups->Add($userGroup);
        $users->Add($user1User);
        $users->Add($user2User);
        $site->GrantGroupMembershipsToUsers($groups, $users);
    } catch (MgException $ex) { }

    $resSvc = $siteConn->CreateService(MgServiceType::ResourceService);
    $resSvc->ApplyResourcePackage($br);

    $srcId = new MgResourceIdentifier("Library://Samples/Sheboygan/Data/Parcels.FeatureSource");
    $dstId = new MgResourceIdentifier("Library://RestUnitTests/Parcels.FeatureSource");
    $resSvc->CopyResource($srcId, $dstId, true);

    $bsWriteable = new MgByteSource(dirname(__FILE__)."/data/Parcels_Writeable.FeatureSource.xml");
    $brWriteable = $bsWriteable->GetReader();
    $resSvc->SetResource($dstId, $brWriteable, null);

    $rdsdfsource = new MgByteSource(dirname(__FILE__)."/data/RedlineLayer.sdf");
    $rdsdfrdr = $rdsdfsource->GetReader();
    $resId = new MgResourceIdentifier("Library://RestUnitTests/RedlineLayer.FeatureSource");

    $rdXml = '<?xml version="1.0"?><FeatureSource xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xsi:noNamespaceSchemaLocation="FeatureSource-1.0.0.xsd"><Provider>OSGeo.SDF</Provider><Parameter><Name>File</Name><Value>%MG_DATA_FILE_PATH%RedlineLayer.sdf</Value></Parameter></FeatureSource>';
    $rdXmlSource = new MgByteSource($rdXml, strlen($rdXml));
    $rdXmlRdr = $rdXmlSource->GetReader();

    $resSvc->SetResource($resId, $rdXmlRdr, null);
    $resSvc->SetResourceData($resId, "RedlineLayer.sdf", MgResourceDataType::File, $rdsdfrdr);
}