<?php
namespace app;

class Index extends \system\BaseController {
    /**
     * 项目类型匹配的文件后缀列表
     * 
     * @var array
     */
    private $langOption = array(
        'html' => array('html', 'js', 'css'),
        'php' => array('php', 'html', 'js', 'css'),
        'c' => array('c', 'h'),
        'python' => array('py'),
    );

    /**
     * 项目类型
     * @var unknown_type
     */
    private $proType = array('html', 'php', 'c', 'python');

    /**
     * 首页
     */
    function index() {
        $this->project();
    }

    function project() {
        if (!isset($_POST['subFlag'])) {
            $pagerNo = isset($_GET['pager']) ? intval($_GET['pager']) : 1;
            $proType = isset($_GET['type']) ? $_GET['type'] : '';
            $folder = isset($_GET['folder']) ? $_GET['folder'] : '';
            $open = isset($_GET['open']) ? $_GET['open'] : '';
            $proPerPage = 10; // 每页显示几条记录
            $offset = ($pagerNo - 1) * $proPerPage;

            $userInfo = $this->getUserInfo();
            $userId = isset($userInfo[0]['id']) ? $userInfo[0]['id'] : 0;
            if (!empty($proType)) {
                $typeStr = "and type='$proType'";
            } else {
                $typeStr = '';
            }
            if (!empty($folder)) {
                $folderStr = "and folder='$folder'";
            } else {
                $folderStr = '';
            }
            if ($open !== '') {
                $openStr = "and is_open=$open";
            } else {
                $openStr = ''; 
            }
            $where = "where user='$userId' {$typeStr} {$folderStr} {$openStr} and status=1";
            $proList = $this->db->find("select id,name,type,description,user,update_time from project
                $where order by update_time limit $offset,$proPerPage");

            // 分页
            $totalPro = $this->db->find("select count(*) as total from project $where");
            $totalPro = $totalPro[0]['total'];
            $totalPages = (int)ceil($totalPro / $proPerPage);
            $pagerNum = 5; // 一共显示几页
            $sideNum = ceil($pagerNum / 2);
            $pagerNumShow = $totalPages >= $pagerNum ? $pagerNum : $totalPages;

            // 边缘探测
            $pagerList = array();
            for ($i = 1; $i <= $pagerNumShow; $i++) {
                if ($pagerNo <= $sideNum) {
                    $pagerList[] = $i;
                } else if ($pagerNo >= $totalPages - $sideNum) {
                    $pagerList[] = $totalPages - ($pagerNumShow - $i);
                }
            }
            if (empty($pagerList)) {
                $pagerList = array($pagerNo - 2, $pagerNo -1, $pagerNo, $pagerNo + 1, $pagerNo +2);
            }
            $prePage = $pagerNo > 1 ? $pagerNo - 1 : 0;
            $nextPage = $pagerNo < $totalPages ? $pagerNo + 1 : 0;
            $pagerInfo = array(
                'pagerNo' => $pagerNo,
                'pagerList' => $pagerList,
                'prePage' => $prePage,
                'nextPage' => $nextPage,
                'totalPages' => $totalPages,
            );

            $folderList = $this->db->find("select * from folder where uid='$userId' and status=1");
            $this->display(
                'project',
                array(
                   'proList' => $proList,
                   'pagerInfo' => $pagerInfo,
                   'proType' => $this->proType,
                   'folderList' => $folderList,
                )
            );
        } else {
            $proName = isset($_POST['name']) ? trim($_POST['name']) : '';
            $proType = isset($_POST['type']) ? trim($_POST['type']) : '';
            $proFolder = isset($_POST['folder']) ? intval($_POST['folder']) : 0;
            $proDesc = isset($_POST['desc']) ? trim($_POST['desc']) : '';
            $now = date('Y-m-d H:i:s');
            // 生成项目记录
            $userInfo = $this->getUserInfo();
            $userId = isset($userInfo[0]['id']) ? $userInfo[0]['id'] : 0;
            $id = $this->db->add("insert into project(name,type,folder,status,user,description,create_time,update_time)
                values('$proName','$proType','$proFolder',1,'$userId','$proDesc','$now','$now')");
            if (!empty($id)) {
                $this->redirect('index.php?a=main&pro_id=' . $id);
            } else {
                show_error('项目创建失败：' . $this->db->error(), '项目创建失败');
            }
        }
    }

    /**
     * 项目控制台页面
     */
    function main() {
        $proId = isset($_GET['pro_id']) ? intval($_GET['pro_id']) : '';

        // 根据proId查询name,type
        $proInfo = $this->db->find("select name,type,folder,description,is_open from project where id='$proId'");
        if (!empty($proInfo)) {
            $proInfo = $proInfo[0];
        } else {
            show_msg("项目{$proId}不存在");
        }
        $proName = $proInfo['name'];
        $proType = $proInfo['type'];
        $proDesc = $proInfo['description'];
        $proFolder = $proInfo['folder'];

        $userInfo = $this->getUserInfo();
        $userId = isset($userInfo[0]['id']) ? $userInfo[0]['id'] : 0;
        $folderList = $this->db->find("select * from folder where uid='$userId'");
        $this->display(
            'main',
            array(
                'proId' => $proId,
                'proName' => $proName,
                'proType' => $proType,
                'proFolder' => $proFolder,
                'proDesc' => $proDesc,
                'langOption' => $this->langOption[$proType],
                'folderList' => $folderList,
            )
        );
    }

    /**
     * 关于
     */
    function about() {
        $this->display('about', array());
    }

    /**
     * 添加目录
     */
    function addDir() {
        $proId = isset($_POST['proId']) ? $_POST['proId'] : 0;
        $parentId = isset($_POST['parentId']) ? $_POST['parentId'] : 0;
        $dirName = isset($_POST['dirName']) ? $_POST['dirName'] : '';

        $now = date('Y-m-d H:i:s');
        $id = $this->db->add("insert into file(pro_id,parent_id,name,type,status,content,create_time,update_time)
                values ('$proId','$parentId','$dirName','dir',1,'','$now','$now')");

        if ($id) {
            $code = 0;
        } else {
            $code = 1;
            $id = 0;
        }
        echo json_encode(array('code' => $code, 'id' => $id));
    }

    /**
     * 更新文件名
     */
    function updateFileName() {
        $id = isset($_POST['id']) ? $_POST['id'] : 0;
        $name = isset($_POST['name']) ? $_POST['name'] : '';
        $ret = $this->db->update("update file set name='$name' where id='$id'");
        if ($ret) {
            $code = 0;
            $msg = '';
        } else {
            $code = 1;
            $msg = 'update error' . $this->db->error();
        }
        echo json_encode(array('code' => $code, 'msg' => $msg));
    }

    /**
     * 更新项目名称
     */
    function updateProName() {
        $id = isset($_POST['proId']) ? $_POST['proId'] : 0;
        $name = isset($_POST['proName']) ? $_POST['proName'] : '';
        $desc = isset($_POST['proDesc']) ? $_POST['proDesc'] : '';
        $folder = isset($_POST['proFolder']) ? $_POST['proFolder'] : 0;
        $isOpen = isset($_POST['isOpen']) ? $_POST['isOpen'] : 0;

        $ret = $this->db->update("update project set name='$name',folder='$folder',description='$desc',is_open=$isOpen where id='$id'");
        if ($ret) {
            $code = 0;
            $msg = '';
        } else {
            $code = 1;
            $msg = 'update error' . $this->db->error();
        }
        echo json_encode(array('code' => $code, 'msg' => $msg));
    }

    /**
     * 获取browser所需数据以及nameMap
     */
    function getCodes() {
        $proId = isset($_POST['proId']) ? intval($_POST['proId']) : '';
        $resultOld = $this->db->find("select id,parent_id,name,type,content,update_time 
            from file where pro_id='$proId' and status=1 order by type,name");

        // 计算含目录的文件名
        $result = $this->getFullPath($resultOld);
        // 计算browser需要的数据
        $browser = $this->getBrowser($result);


        $nameMap = array();
        foreach ($result as $key => $value) {
            if ($value['type'] === 'dir') {
                continue;
            }
            $nameMap[$value['fullPath']] = array(
                'fullPath' => $value['fullPath'],
                'name' => $value['name'], // 文件名(不含目录)
                'codes' => $value['content'], // 文件代码
                'id' => $value['id'], // 文件id
                'type' => $value['type'], // 文件类型
            );
        }

        $proInfo = $this->db->find("select * from project where id='$proId' and status=1");
        $browser = array(
            array(
                'fileId' => -1, // -1 仅仅是为了标注这是一个项目
                'text' => $proInfo[0]['name'],
                'type' => 'project',
                'icon' => 'glyphicon glyphicon-cloud dir-icon',
                'nodes' => $browser,
                // 'state' => array('expanded' => 0),
            )
        );
        echo json_encode(array('browser' => $browser, 'nameMap' => $nameMap));
    }

    /**
     * 处理文件名,和目录名合并
     * 
     * @param array $result
     * @param int $id 文件id
     * 
     * @return array/string 含长文件名的文件信息
     */
    private function getFullPath($result, $id = 0) {
        if (empty($id)) {
            foreach ($result as $k => $v) {
                if (!empty($v['parent_id'])) {
                    $result[$k]['fullPath'] = $this->getFullPath($result, $v['parent_id']) . $result[$k]['name'];
                } else {
                    $result[$k]['fullPath'] = $v['name'];
                }
            }
            return $result;
        } else {
            foreach ($result as $k => $v) {
                if ((int)$v['id'] === (int)$id) {
                    if (!empty($v['parent_id'])) {
                        return  $this->getFullPath($result, $v['parent_id']) . $v['name'] . '/';
                    } else {
                        return $v['name'] . '/';
                    }
                }
            }
        }
    }

    /**
     * 用递归获取browser data
     * 
     * @param array $result 待处理的data
     * @param int $parentId 父节点id
     * 
     * @return array $browser
     */
    private function getBrowser($result, $parentId = 0) {
        $browser = array();
        foreach ($result as $key => $value) {
            if ((int)$value['parent_id'] === (int)$parentId) {
                $tmpArr = array(
                    // 'tags' => array('<a href="#">删除</a>'),
                    'fullPath' => $value['fullPath'],
                    'fileId' => $value['id'],
                    'text' => $value['name'],
                    'type' => $value['type'],
                    'fatherId' => $value['parent_id'],
                    'icon' => $value['type'] === 'dir' ? 'glyphicon glyphicon-book dir-icon' : 'glyphicon glyphicon-file file-icon',
                    'nodes' => $this->getBrowser($result, $value['id']),
                );
                if ($value['type'] === 'dir') {
                    $tmpArr['state'] = array('expanded' => 1);
                }
                $browser[] = $tmpArr;
            }
        }
        $browser = empty($browser) ? '' : $browser;
        return $browser;
    }

    function stopContainer() {
        $proId = isset($_POST['proId']) ? intval($_POST['proId']) : 0;
        $proName = isset($_POST['proName']) ? trim($_POST['proName']) : '';
        $this->db->update("update gateone set status=0 where proId='$proId' and status=1");
        $identifier = $proName . '_' . $proId;
        $execStr = "docker stop $identifier && docker rm $identifier";
        $ret = system($execStr);
        if ($ret !== false) {
            ob_clean();
            echo json_encode(array('code' => 0, 'msg' => ''));
        } else {
            echo json_encode(array('code' => 1, 'msg' => 'stop failed'));
        }
    }

    function runCode() {
        // $userInfo = $this->getUserInfo();
        // if (isset($userInfo[0]['status']) && (int)$userInfo[0]['status'] === 2 || $this->isOpen) {
        $codes = isset($_POST['codes']) ? json_decode($_POST['codes'], true) : '';
        $proId = isset($_POST['proId']) ? intval($_POST['proId']) : 0;
        $proName = isset($_POST['proName']) ? trim($_POST['proName']) : '';
        $type = isset($_POST['type']) ? trim($_POST['type']) : '';

        $identifier = $proName . '_' . $proId;
        $dir = ROOT . "app/sandbox/{$identifier}/source/";
        // 根据codes生成文件并执行
        if (file_exists($dir)) {
            // 删除目录内的所有内容
            \app\lib\File::deleteDir($dir);
        }

        // 代码都是经过转义的,因此这里需要反转义
        foreach ($codes as $file => $code) {
            \app\lib\File::writeData(str_replace('\\', '/', $dir . $file), stripslashes($code));
        }
        $url = "sandbox.php?name={$identifier}&type={$type}";
        if ($type === 'php') {
            $execStr = "docker run -v " . ROOT . "app/sandbox/{$identifier}:/root/data/codelab/{$identifier} --name {$identifier} yequjinxin/php:v1.00 php /root/script/exec_code.php {$identifier}";
            $ret = system($execStr);
            if ($ret !== false) {
                // 清空container
                // system("docker stop $identifier");
                system("docker rm $identifier");
                ob_clean();
                echo json_encode(array('code' => 0, 'msg' => '', 'data' => $url));
            } else {
                echo json_encode(array('code' => 2, 'msg' => 'run error'));
            }
        } else if ($type === 'html') {
            echo json_encode(array('code' => 0, 'msg' => '', 'data' => $url));
        } else if ($type === 'c' || $type === 'python') {
            $gateone = $this->db->find('select id,port from gateone where status=0 limit 1');
            if (empty($gateone[0])) {
                echo json_encode(array('code' => 3, 'msg' => 'no more OS resource'));
                exit;
            }
            $port = $gateone[0]['port'];
            $execStr = "docker run -v " . ROOT . "app/sandbox/{$identifier}/source:/root/{$proName} -d -p $port:$port --name {$identifier} yequjinxin/gateone:v1.00 /bin/sh -c '/usr/local/bin/run.sh $port'";
            $ret = system($execStr);
            if ($ret !== false) {
                ob_clean();
                $now = date('Y-m-d H:i:s');
                $id = $gateone[0]['id'];
                $this->db->update("update gateone set status=1,proId='$proId',begin_time='$now' where id='$id'");
                echo json_encode(array('code' => 0, 'msg' => '', 'port' => $port));
            } else {
                echo json_encode(array('code' => 2, 'msg' => 'run error'));
            }
        }
        // } else {
        //     echo json_encode(array('code' => 1, 'msg' => '当前用户没有运行权限'));
        // }
    }

    private function getFIleListById($fileList, $parentId) {
        $ret = array();
        foreach ($fileList as $k => $v) {
            if ($v['id'] == $parentId) {
                $ret[] = $v['name'];
                $retParent = $this->getFIleListById($fileList, $v['parent_id']);
                if (!empty($retParent)) {
                    $ret = array_merge($ret, $retParent);
                }
                break;
            }
        }
        return $ret;
    }

    function saveCode() {
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $fileName = isset($_POST['fileName']) ? trim($_POST['fileName']) : '';
        $type = isset($_POST['type']) ? trim($_POST['type']) : '';
        $content = isset($_POST['content']) ? trim($_POST['content']) : '';
        $proId = isset($_POST['proId']) ? intval($_POST['proId']) : '';
        $proName = isset($_POST['proName']) ? trim($_POST['proName']) : '';
        $parentId = isset($_POST['parentId']) ? intval($_POST['parentId']) : 0;

        $fileList = $this->db->find("select * from file where pro_id='$proId' and status=1");
        $filePath = $this->getFIleListById($fileList, $parentId);
        array_unshift($filePath, $fileName);
        $identify = $proName . '_' . $proId;
        \app\lib\File::writeFile($filePath, stripslashes($content), $identify);

        $now = date('Y-m-d H:i:s');
        if (empty($id)) {
            // insert
            $ret = $this->db->add("insert into file(pro_id,parent_id,name,type,status,content,create_time,update_time)
                values('$proId','$parentId','$fileName','$type',1,'$content','$now','$now')");
        } else {
            // update
            $ret = $this->db->update("update file set parent_id='$parentId',content='$content',name='$fileName',update_time='$now' where id='$id'");
        }
        $proRet = $this->db->update("update project set update_time='$now' where id='$proId'");
        $arr = array();
        if ($ret && $proRet) {
            $arr = array('code' => 0, 'id' => $ret);
        } else {
            $arr = array('code' => 1);
        }
        echo json_encode($arr);
    }

    function delFile() {
        $id = isset($_POST['id']) ? $_POST['id'] : 0;
        $type = isset($_POST['type']) ? $_POST['type'] : '';
        if (!empty($id)) {
            if ($type !== 'dir') {
                $ret = $this->db->update("update file set status=0 where id='$id'");
            } else {
                $ret = $this->db->update("update file set status=0 where id='$id' or parent_id='$id'");
            }
        }
        if (empty($ret)) {
            $code = 1;
            $msg = 'delete error';
        } else {
            $code = 0;
            $msg = '';
        }
        echo json_encode(array('code' => $code, 'msg' => $msg));
    }

}
