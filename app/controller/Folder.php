<?php
namespace app;

class Folder extends \system\BaseController {
    /**
     * folder
     */
    function index() {
        $this->display('folder', array());
    }

    /**
     * 获取自定义类型列表
     */
    function getFolderList() {
        $userInfo = $this->getUserInfo();
        $userId = isset($userInfo[0]['id']) ? $userInfo[0]['id'] : 0;
        $folderList = $this->db->find("select id,name,description from folder where uid='$userId' and status=1 order by create_time desc");

        $total = $this->db->find("select count(*) as total from folder where uid='$userId'");
        $total = $total[0]['total'];
        echo json_encode(array('rows' => $folderList, 'total' => $total));
    }

    function deleteFolder() {
        $id = isset($_POST['folderId']) ? $_POST['folderId'] : 0;
        $ret = $this->db->update("update folder set status=9 where id='$id'");
        if ($ret) {
            $code = 0;
            $msg = '';
        } else {
            $code = 1;
            $msg = $this->db->error();
        }
        echo json_encode(array('code' => $code, 'msg' => $msg));
    }

    function addFolder() {
        $userInfo = $this->getUserInfo();
        $userId = isset($userInfo[0]['id']) ? $userInfo[0]['id'] : 0;
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $desc = isset($_POST['desc']) ? trim($_POST['desc']) : '';
        $now = date('Y-m-d H:i:s');
        $ret = $this->db->add("insert into folder (uid,name,description,status,create_time) values('$userId','$name','$desc',1,'$now')");
        if ($ret) {
            $code = 0;
            $msg = '';
        } else {
            $code = 1;
            $msg = $this->db->error();
        }
        echo json_encode(array('code' => $code, 'msg' => $msg));
    }

    function updateFolder() {
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $desc = isset($_POST['desc']) ? trim($_POST['desc']) : '';
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $ret = $this->db->update("update folder set name='$name',description='$desc' where id='$id'");
        if ($ret) {
            $code = 0;
            $msg = '';
        } else {
            $code = 1;
            $msg = $this->db->error();
        }
        echo json_encode(array('code' => $code, 'msg' => $msg));
    }
}
