<?php

require_once "controller.php";

class MgTileServiceController extends MgBaseController {
    public function __construct($app) {
        parent::__construct($app);
    }

    public function GetTile($resId, $groupName, $scaleIndex, $row, $col) {
        $resIdStr = $resId->ToString();
        $that = $this;
        $this->EnsureAuthenticationForHttp(function($req, $param) use ($that, $resIdStr, $groupName, $scaleIndex, $row, $col) {
            $param->AddParameter("OPERATION", "GETTILEIMAGE");
            $param->AddParameter("VERSION", "1.2.0");
            $param->AddParameter("MAPDEFINITION", $resIdStr);
            $param->AddParameter("BASEMAPLAYERGROUPNAME", $groupName);
            $param->AddParameter("SCALEINDEX", $scaleIndex);
            $param->AddParameter("TILEROW", $row);
            $param->AddParameter("TILECOL", $col);
            $that->ExecuteHttpRequest($req);
        }, true); //Tile access can be anonymous, so allow for it if credentials/session specified
    }
}

?>