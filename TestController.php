<?php

class TestController extends Controller
{
    public function init(){
        parent::init();
        $this->layout='//layouts/test';
        header("Content-type: text/html; charset=utf-8");
    }

    public function actionNet()
    {
        $param=print_r($_REQUEST,true);
        file_put_contents(ROOT_PATH.'/a.txt',$param);
        echo $param;
    }

    public function actionYzhd()
    {


        $yzhd = new Yzhd();
        $yzhd->goods(33496);

    }

    public function actionQrcode()
    {
        Yii::import('ext.qrcode.ZkQrcode');
        $obj = new ZkQrcode();
        $img = $obj->generator('http://www.baidu.com','1.png','H',8,'wx2.png');

        echo '<img src="'.Yii::app()->params['domain'].'/qrcode/images/'.$img.'" />';
    }

    protected function post($url,$data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $output = curl_exec($ch);
        curl_close($ch);

        return $output;
    }

    /**
     * 发货接口测试
     */
    public function actionDlv()
    {
        $orders=trim($_GET['orders']);  if(empty($orders))  $this->message('缺少订单号参数orders');
        $dname=trim($_GET['dname']);    if(empty($dname))  $this->message('缺少物流名称参数dname');
        $dcode=trim($_GET['dcode']);    if(empty($dcode))  $this->message('缺少物流编号参数dcode');

        $url = Yii::app()->params['domain']."/webServer/deliver";
        $post_data = array('orders_sn' => $orders, 'dname' =>$dname , 'dcode' =>$dcode, 'zksign' => strtoupper( md5($orders.Yii::app()->params['zksign']) ) );
        //$output = $this->post($url,$post_data);
        $output=@file_get_contents($url."?".http_build_query($post_data));
        print_r($output);
    }

    /**
     * 配货接口测试
     */
    public function actionDstr()
    {
        $orders=trim($_GET['orders']);  if(empty($orders))  $this->message('缺少订单号参数orders');

        $url = "http://192.168.14.249/webServer/distribution";
        $post_data = array('orders_sn' => $orders, 'zksign' => strtoupper( md5($orders.Yii::app()->params['zksign']) ) );
        //$output = $this->post($url,$post_data);
        $output=@file_get_contents($url."?".http_build_query($post_data));
        print_r($output);
    }

    /**
     * 领用抵金券
     */
    public function actionCoupon()
    {
        $this->layout='min';
        $coupons = PromotionFavorBatch::model()->findAllByAttributes(array('promotion_batch_status'=>0, 'promotion_batch_activation'=>1));
        $this->render('coupon',array('coupons'=>$coupons));
    }

    /**
     * 配货压力测试
     */
    public function actionDstryl()
    {
        $time1 = trim($_GET['time1']);
        $time2 = trim($_GET['time2']);

        if( $time2 && $time1 ){
            $sql = "SELECT orders_sn FROM {{orders}}  WHERE orders_addtime BETWEEN '".$time1." 00:00:00' AND '".$time2." 23:59:59' ORDER BY orders_addtime desc";
            $v_sn = Yii::app()->db->createCommand( $sql )->queryColumn();
            $orders = implode( ',', $v_sn );
            $url = "http://192.168.14.249/webServer/distribution";
            $post_data = array('orders_sn' => $orders, 'zksign' => strtoupper( md5($orders.Yii::app()->params['zksign']) ) );
            $output = $this->post($url,$post_data);
            print_r($output);
        }else{
             $this->message('缺少订单号参数time参数');
        }
        
    }

    //主从查询测试
    public function actionSelect(){
        $db=&Yii::app()->db;
        $sql="SELECT * FROM zk_member WHERE member_loginmobile='18351887910'";
        //$db->createCommand($sql)->queryRow();

        $sql="SELECT * FROM zk_member a inner join zk_member_profile b ON a.member_id=b.member_profile_memberid WHERE a.member_loginmobile='18351887910'";
        //$db->createCommand($sql)->queryRow();

        //$db->createCommand()->select('*')->from('sph_counter')->where('counter_id=3')->queryRow();

        //$db->createCommand()->select('*')->from('zk_member c')->join('zk_member_profile d','c.member_id=d.member_profile_memberid')->where("c.member_loginmobile='18351887910'")->queryRow();

        //$a=ProductBasic::model()->findByPk(1000);

        $b=Member::model()->with('mPf')->findByPk(1000);

        $this->render('/system/test');
    }

    //主从插入测试
    public function actionInsert(){
        $db=&Yii::app()->db;

        //insert测试
        //$db->createCommand('insert into sph_counter(counter_id,max_doc_id) values (3,200)')->execute();
        $db->createCommand('replace into sph_counter(counter_id,max_doc_id) values (3,300)')->execute();

        //$db->createCommand()->insert('sph_counter',array('counter_id'=>4,'max_doc_id'=>500));

        $helpCate=new HelpCate();
        $helpCate->help_cate_name='数据库同步';
        $helpCate->save();

        $this->render('/system/test');
    }

    //主从修改测试
    public function actionUpdate(){
        $db=&Yii::app()->db;

        $db->createCommand('update sph_counter set max_doc_id=max_doc_id+1 where counter_id=3')->execute();

        $this->render('/system/test');
    }

    //主从删除测试
    public function actionDelete(){
        $db=&Yii::app()->db;

        $db->createCommand('delete FROM sph_counter where counter_id=3')->execute();

        $this->render('/system/test');
    }

    public function actionArtDlg(){
        //$this->layout='//layouts/column1';
        $this->render('/system/test');
    }

    public function actionSms(){

        $bandPar='18351887900';
        $pwd='123456';

        $sms=new Sms();
        $msg=Yii::app()->config->get('autoReg','sms');
        $msg=sprintf($msg,$pwd);
        $sms->sendMsg($bandPar,$msg);

        $this->message('短信发送成功');

        /*$sms=new Sms();
        $ret=$sms->sendMsg('18351887900','123【先声再康】');
        if($ret){
            $this->message('发送成功');
        }else{
            $this->message('发送失败');
        }*/
    }

    public function actionConfig(){

        $key=trim($_GET['key']);
        $module=$_GET['module'] ? trim($_GET['module']) : 'system';
        $value=Yii::app()->config->get($key,$module);
        echo 'module='.$module.',key='.$key.',value='.$value;
    }

    public function actionServerNotice(){
        $tels='18351887900';
        $content='123尊敬的会员你好回复TD退订123';
        $time=time();
        $rand=md5($time.Yii::app()->params['zksign']);
        $url = "http://www.zk100.com/webServer/serverNotice";
        $post_data = array('tels' => $tels, 'content' => $content, 'time'=>$time, 'rand'=>$rand);
        $output = $this->post($url,$post_data);
        print_r($output);
    }

    public function actionTransaction(){
        $connection=&Yii::app()->db;
        $transaction=$connection->beginTransaction();
        try
        {
            /*$member=Member::model()->findByPk(724023);
            $member->member_nickname=$member->member_nickname.'8';
            $member->save();

            echo "==保存<br />";*/

            //$sql='';
            //$connection->createCommand($sql)->execute();

            Member::model()->deleteByPk(2466);
            echo "==删除<br />";
            throw   new Exception();

            $transaction->commit();
        }
        catch(Exception $e)
        {
            $transaction->rollBack();
            exit("==回滚<br />");
        }
    }


    //神马订单同步
    public function actionShmOrder()
    {

        $orders = Orders::model()->findByPk($_GET['oid']);
        if($orders){
            $shm = new Shm();
            $arr = array(
                'OrderId'=>$orders->orders_sn
            );
            $ret = $shm->orderCancel($arr);
            //if($ret)   $this->ajaxResponse(array('code' => 1, 'msg' => '全球购订单同步失败，请联系客服！'));
            //$ret = $orders->sendInfoToShm();
            var_dump($ret);
        }else{
            $this->message('订单不存在');
        }

    }

    public function actionClick(){
        Yii::import('ext.pinyin.*');
        $pinyin=new Pinyin();
        $str="乙型肝炎病毒标志物(HBsAg、HBsAb、HBeAg、HBeAb、HBcAb)检测试剂盒(乳胶法)";
        $ret = $pinyin->trans($str,array('delimiter'=>' ', 'accent'=>false, 'only_chinese'=>true, 'uppercase'=>true));
        echo $str."===>".$ret."<br />";
        $ret = $pinyin->letter($str,array('delimiter'=>' ', 'accent'=>false, 'only_chinese'=>false, 'uppercase'=>true));
        echo $str."===>".$ret."<br />";

        $pinyin=new Pin();
        $str="乙型肝炎病毒标志物(HBsAg、HBsAb、HBeAg、HBeAb、HBcAb)检测试剂盒(乳胶法)";
        $ret = $pinyin->Pinyin($str,'UTF-8');
        echo $str."===>".$ret."<br />";
        $ret = $pinyin->simple_spell;
        echo $str."===>".$ret;

        $this->render('/system/test');
    }

    public function actionTs()
    {

        $db = & Yii::app()->db;

        $b = strtotime('2016-04-01');

        $e = strtotime('2016-05-20');
        echo '<pre>';
        for($i=$b; $i<=$e; $i+=86400){
            $j = date('Y-m-d', $i);
            $k = date('Y-m-d', $i+86400);

            $limit = rand(3,10);

            $result = $db->createCommand("SELECT orders_sn FROM zk_orders WHERE orders_from_id=9 AND orders_addtime>=:b AND orders_addtime< :e ORDER BY RAND() LIMIT $limit")->queryColumn(array(":b"=>$j, ":e"=>$k));

            foreach($result as $osn)    echo $osn.'<br />';

            //print_r($result);

        }
        echo '<pre>';

    }

    public function actionXl(){
        echo 1;die;
    }
    public function actionTq()
    {
        $this->render('tq');
    }

    //测试erp新增或编辑订单
    public function actionErp(){
        $stype = isset($_GET['stype'])?$_GET['stype']:0;
        //echo $stype.'123';die();
        Orders::model()->erpSycnaddOredit($stype);
    }

    //测试erp客审 和 发货
    public function actionErps(){
        $stype = isset($_GET['stype'])?$_GET['stype']:1;
        Orders::model()->erpSycnksorfh($stype);
    }

    public function actionTests(){
        $str = '19电联不上
20短信通知21明天会尽快支付的呢22说晚上支付';
        echo preg_replace('/\s*/', '', $str);
    }

    public function  actionSimilary()
    {
        $text1 = "我喜欢看电视，很牛逼，不喜欢看电影。";
        $text2 = "我喜欢看电视，不喜欢看电影。这么强悍";

        Yii::import('application.extensions.textsimilarity.TextSimilarity');
        $obj = new TextSimilarity ($text1, $text2);
        echo $obj->run();
    }


    // public function actionCategory()
    // {

    //     $category = array(
    //         '专科用药' => array(
    //             '男科用药'=>'阳痿早泄|泌尿生殖|少精无精|脱发少发|补肾壮阳|滋阴补肾|尿路感染|抗菌药物|尿路结石|肾炎|肾病综合症|前列腺增生|前列腺炎|尿崩症',
    //             '肿瘤科药'=>'肺癌|肝癌|乳腺癌|广谱抗癌|癌症疼痛|移植用药|肿瘤辅助|增强免疫|升白升血小板|皮质激素',
    //             '呼吸科药'=>'肺炎|咳嗽|肺气肿|尘肺矽肺|慢性阻塞性肺病|肺结核|急慢咽炎|扁桃体炎|上呼吸道感染',
    //             '皮肤性病'=>'银屑病|白癜风|灰指甲|皮炎湿疹|皮肤癣症|痤疮痘痘|色斑疤痕|头发健康|尖锐湿疣|手足皲裂|蚊虫叮咬|淋病|生殖器疱疹',
    //             '风湿关节'=>'痛风|跌打损伤|风湿骨痛|骨质增生|骨质疏松|腰肌劳损|颈椎病|肩周炎|类风湿性关节炎|贴膏喷剂|补钙营养',
    //             '心脑血管'=>'高血压|高血脂|中风偏瘫|冠心病|记忆力减退|心脑血管综合用药',
    //             '肝胆科药'=>'乙肝用药|乙肝辅助|急慢性肝炎|脂肪肝|肝纤维化|肝硬化|急慢胆囊炎|利胆排石|黄疸|疏肝理气|解酒护肝',
    //             '胃肠消化'=>'胃炎用药|腹痛腹泻|胃肠感染|克罗恩病|消化不良|消化道溃疡|肠道菌群紊乱|便秘|痔疮|驱虫类药|结肠炎|调理脾胃',
    //             '神经科药'=>'帕金森类|老年痴呆|面瘫癫痫|神经衰弱|精神分裂|抑郁症|焦虑症|眩晕症|睡眠障碍|记忆减退|多动症|肌无力|偏头疼',
    //             '内分泌药'=>'西药降糖|中药降糖|甲亢|糖尿病护理|神经损伤',
    //             '妇科用药'=>'月经不调|乳腺疾病|不孕不育|日常避孕|安胎养生|卵巢养护|更年期综合征|产后疾病|阴道炎|宫颈炎|盆腔炎',
    //         ),

    //         '日常用药' => array(
    //             '感冒退热'=>'流行性感冒|病毒性感冒|发热退烧|消炎镇痛',
    //             '维矿补充'=>'补充钙剂|补充锌剂|补充氨基酸|维生素类|电解矿物',
    //             '五官用药'=>'眼部炎症|视力疲劳|近视眼|白内障|青光眼|眼部病变|急慢鼻炎|口腔护理|急慢咽炎|护嗓开音|扁桃体炎|口腔溃疡|牙龈肿痛|耳聋耳鸣',
    //             '儿科用药'=>'感冒发热|咳嗽化痰|消化不良|小儿腹泻|驱虫|补钙补锌|维生素类|自汗盗汗',
    //         ),

    //         '医疗器械'=>array(
    //             '检测器材'=>'血压计|血糖仪|胎心仪|体重及脂肪检测|血氧仪|听诊器|计步器',
    //             '康复理疗'=>'牵引器|理疗贴膏|针灸|拔罐|理疗仪|刮痧板|仪器耗材',
    //             '辅助器具'=>'助听器|制氧机|轮椅|助行器|拐杖|打鼾呼吸机',
    //             '家庭药箱'=>'体温计|口罩|药箱|脱脂棉|纱布|皮肤消毒|棉签|创口贴|绷带|一次性手套',
    //             '检测试纸'=>'早孕试纸|排卵试纸|尿糖试纸|家用试纸|特殊检测试纸',
    //             '保健养生'=>'足浴盆|足疗浴盐|按摩器|按摩油',
    //         ),

    //         '两性幸福'=>array(
    //             '成人情趣'=>'男用器具|女用器具|情趣内衣',
    //             '计生用品'=>'安全套|私密护理|润滑油',
    //         ),

    //         '滋补保健'=>array(
    //             '基础营养'=>'多维生素|矿物质|蛋白营养',
    //             '妇婴健康'=>'叶酸|矿物质类|维生素类|婴幼奶粉|益生菌',
    //             '孝敬父母'=>'改善三高|调节免疫|改善睡眠|骨质疏松',
    //             '强壮男人'=>'滋补肝肾|滋阴补肾|综合营养|蛋白质粉',
    //             '靓丽女人'=>'补血益气|排毒养颜|瘦身纤体|润肠通便',
    //             '中药滋补'=>'参茸贵细|阿胶|中药饮片|养生花茶|滋补礼盒',
    //         ),

    //         '美妆日化'=>array(
    //             '面部清洁'=>'洁面乳|洁面皂|面部去角质|卸妆产品|黑头清洁',
    //             '面部护理'=>'化妆水|精华液|乳液|日霜|晚霜|隔离霜|防晒露|修复液|面膜|润唇膏|彩妆',
    //             '口腔护理'=>'漱口消炎|牙齿护理|祛除口气',
    //             '眼部护理'=>'眼霜|眼贴|眼部护理液',
    //             '头发护理'=>'洗发护发|防脱生发|美发焗油',
    //             '身体护理'=>'手足护理|香氛香体|脱毛靓肤|减肥塑身|疤痕修复|抗菌止痒|防冻防裂',
    //             '美妆专柜'=>'薇姿|片仔癀|雅漾|理肤泉|芙丽芳丝',
    //             '居家生活'=>'衣物清洁|家居环境清洁|家用纸品|日用杂货',
    //         ),
    //         '健康母婴'=>array(
    //             '母婴食品'=>'奶粉|辅食|营养保健',
    //             '母婴用品'=>'奶瓶奶嘴|喂食餐具|尿裤尿垫|清洁除菌|幼儿口腔|婴童护肤|婴童洗护|居家健康|驱蚊产品',
    //         ),
    //     );

    //     $db = &Yii::app()->db;
    //     $insert = "INSERT INTO {{category}} (cate_typeid,cate_parentid,cate_name,cate_producttypeid,cate_sort,cate_isfrequently,cate_isactive) VALUES (:tid,:pid,:name,:pttid,:sort,:ifrq,:itve)";
    //     foreach( $category as $k => $v ){
    //         $db->createCommand($insert)->execute(array(':tid'=>3,':pid'=>0,':name'=>$k,':pttid'=>0,':sort'=>1,':ifrq'=>1,':itve'=>1));
    //         if( is_array($v) ){
    //             $level0 = Yii::app()->db->getLastInsertID(); 
    //             foreach( $v as $ka => $vb ){
    //                 $db->createCommand($insert)->execute(array(':tid'=>3,':pid'=>$level0,':name'=>$ka,':pttid'=>0,':sort'=>1,':ifrq'=>1,':itve'=>1));
    //                 $level1 = Yii::app()->db->getLastInsertID();
    //                     $cates = array();
    //                     $cates = explode('|', $vb);
    //                 if( is_array($cates) ){
    //                     foreach( $cates as $ko => $vo ){
    //                         $db->createCommand($insert)->execute(array(':tid'=>3,':pid'=>$level1,':name'=>$vo,':pttid'=>0,':sort'=>1,':ifrq'=>1,':itve'=>1));
    //                     }
    //                 }
                    
    //             }
    //         }
    //     }
    //     echo 'success';exit;

    // }

    // public function actionPcate()
    // {
    //     $db = &Yii::app()->db;
    //     $cate_level0 = "SELECT * FROM {{category}} WHERE cate_parentid = 0";
    //     $cate_level1 = "SELECT * FROM {{category}} WHERE cate_parentid = :cid";
    //     $search = "SELECT cate_id FROM {{category_copy}} WHERE cate_name = :name";

    //     $category_l0 = $category_l1 = $category_l2 = array();
    //     $category_l0 = $db->createCommand($cate_level0)->queryAll(true);
    //     foreach($category_l0 as $ck => $cv){
    //         $category_l1 = $db->createCommand($cate_level1)->queryAll(true,array(':cid'=>$cv['cate_id']));
    //         if( is_array($category_l1) ){
    //             foreach($category_l1 as $ka => $va ){
    //                 $category_l2 = $db->createCommand("SELECT cate_id,cate_name FROM {{category}} WHERE cate_id IN (".$va['cate_childids'].")")->queryAll(true);
    //                 if( is_array($category_l2) && count($category_l2) ){
    //                     foreach($category_l2 as $row){
    //                         $res = $db->createCommand($search)->queryColumn(array(':name'=>$row['cate_name']));
    //                         if( $res && count($res) ){
    //                             $cids = implode(',',$res);  //在备份表中和新表中存在的分类ID
    //                             echo $row['cate_id'].'---'.$row['cate_name'].'----'.$cids.'<br/>';exit;
    //                             $db->createCommand("UPDATE {{product_basic}} SET product_fanlib = 1, product_cateid = ".$row['cate_id']." WHERE product_fanlib = 0 AND product_cateid IN (".$cids.")")->execute();
    //                             $db->createCommand("UPDATE {{category}} SET cate_site = 1 WHERE cate_id =".$row['cate_id'])->execute();
    //                         }
    //                     }
    //                 }
    //             }
    //         }
    //     }

    // }

    // public function actionTestIp(){
    //     $ip = Fun::getIp();
    //     $companyIp=Yii::app()->config->get('companyIp');
    //     echo 'IP:'.$ip.'---'.$companyIp.'<br/>';
    //     if( $ip != $companyIp ) {
    //         echo '不等于';
    //     }else{
    //         echo '等于';
    //     }
    // }

    public function actionBrowser()
    {

        $browser=EvtCnt::model()->getBrowser();
        $browser=$browser[0].$browser[1];
        print_r($browser);
    }

    /**
     * 测试海外购订单
     */
    public function actionHmtOrder()
    {

        $order = Orders::model()->findByPk($_GET['id']);
        if($order){
            if($order->orders_isoverseas){
                $ret = $order->sendInfoToHmt();
                if($ret){
                    $this->message('执行成功');
                }else{
                    $this->message('执行失败');
                }
            }else{
                $this->message('订单不是海外购订单');
            }

        }else{
            $this->message('订单不存在');
        }

    }

    // //商品分类修改
    // public function actionProducts()
    // {
    //     set_time_limit(0);
    //     $opt = isset($_REQUEST['opt']) ? $_REQUEST['opt'] : '';
    //     $db = Yii::app()->db;
    //     if( $opt == 's2' ){
    //         if ( $_FILES["xlsx"]["type"] == "application/vnd.ms-excel" ){
    //             $inputFileType = 'Excel5';
    //         }elseif ( $_FILES["xlsx"]["type"] == "application/octet-stream" || $_FILES["xlsx"]["type"] == "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" ){
    //             $inputFileType = 'Excel2007';
    //         }else {
    //             $this->message('上传文件类型有误', $this->createUrl('floor/salesms'));    
    //         }
    //         if ($_FILES["xlsx"]["error"] > 0) {
    //             $this->message('Error:'.$_FILES["xlsx"]["error"], $this->createUrl('floor/salesms'));
    //         }
    //         if (empty($_FILES["xlsx"]["name"])) {
    //             $this->message('上传文件不存在!', $this->createUrl('floor/salesms'));
    //         }
    //         if ($_FILES["xlsx"]["size"] > 3 * 1024 * 1024 || $_FILES["xlsx"]['size'] == 0) {
    //             $this->message('上传文件大小不能超过3M且不能为0M!', $this->createUrl('floor/salesms'));
    //         }
    //         $filename = './upload/file/'.$_FILES["xlsx"]['name'];
    //         if( file_exists( $filename ) ){
    //             unlink($filename); //如果存在相同文件则删除
    //         }

    //         move_uploaded_file($_FILES["xlsx"]["tmp_name"], $filename);

    //         ini_set("memory_limit", "1024M");
    //         $PHPExcel = Fun::phpExcel();
    //         $objReader = PHPExcel_IOFactory::createReader($inputFileType);
    //         try{
    //             $objPHPExcel = $objReader->load($filename);
    //         }catch(Exception $e){
    //             $this->message('你太调皮了!', $this->createUrl('floor/salesms'));
    //         }
            
    //         $objWorksheet = $objPHPExcel->getActiveSheet();
    //         $highestRow = $objWorksheet->getHighestRow(); //总行数
    //         $highestColumn = $objWorksheet->getHighestColumn();
    //         $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);//总列数
    //         for ($row = 2;$row <= $highestRow;$row++){
    //             $data[$row-2]['id'] = $objWorksheet->getCellByColumnAndRow(0, $row)->getValue();
    //             $data[$row-2]['cid'] = $objWorksheet->getCellByColumnAndRow(1, $row)->getValue();
    //         }
    //         $arr = count($data);
    //         if( $arr == 0 ){
    //             $this->message('上传文件的内容不能为空!', $this->createUrl('floor/salesms'),5);
    //         }
    //         $valid_data = array();
    //         $sql = "SELECT product_code FROM {{product_basic_copy1}} WHERE product_id = :pid";
    //         $update = "UPDATE {{product_basic_copy1}} SET product_cateid = :cid WHERE product_id = :pid";
    //         foreach ($data as $k => $v) {
    //             if( is_array($v) && array_filter($v) ){
    //                 $res = $db->createCommand($sql)->queryAll(true,array(':pid'=>$v['id']));
    //                 if( $res && count($res) ){
    //                     $db->createCommand($update)->execute(array(':pid'=>$v['id'],':cid'=>$v['cid']));
    //                 }else{
    //                     continue;
    //                 }

    //             }
    //         }
    //         $this->message("商品分类更新成！");
    //     }
        
    //     $this->render('tq');
    // }
    // 
    
    // //分类缓存
    // public function actionCacheCate()
    // {
    //     Yii::app()->cache->delete('categoryAll');
    //     $categories = Category::model()->readCache('categoryAll');
    //     echo '<pre>'; print_r($categories);exit;
    // } 
    // 
    
    //修复列表页商品今日关注
    // public function actionRelateProduct()
    // {
    //     $db = &Yii::app()->db;
    //     $relateProduct = $db->createCommand("SELECT product_name FROM {{product_relatetag_copy}} WHERE product_relatetag_productid is null")->queryAll();
    //     // echo '<pre>';print_r($relateProduct);exit;
    //     if( is_array($relateProduct) && count($relateProduct) ){
    //         foreach ($relateProduct as $k => $v) {
    //             $name = $v['product_name'];
    //             $sql = 'SELECT product_cateid,product_id FROM {{product_basic}} WHERE product_name LIKE "%'.$v['product_name'].'%" ';
    //             $up = 'UPDATE {{product_relatetag_copy}} SET product_cateid = :cid,product_relatetag_productid=:pid WHERE product_name LIKE "%'.$v['product_name'].'%" ';
    //             $product = $db->createCommand($sql)->queryAll();
    //             if( $product && array_filter($product) ){
    //                 $db->createCommand($up)->execute(array(':cid'=>$product['0']['product_cateid'],':pid'=>$product['0']['product_id']));

    //             }else{
    //                 continue;
    //             }
    //         }
    //         echo "success";
    //     }


    //}
    //


    private function segment($text)
    {
        $outText = array();
        //实例化
        $so = new Scws();
        //处理
        $ret = $so->send_text($text);
        if( is_array($ret) ){
            foreach( $ret as $v ){
                $outText[] = iconv("GBK", "UTF-8", $v);
            }
        }
        return $outText;
    }

    public function actionaskTest()
    {
        $sphinx=Yii::createComponent(Yii::app()->params['sphinx']);
        $sphinx->init();
        $cl=$sphinx->client();

        $keyword = "刚出生半个月的小孩这两天出现轻度咳嗽而且喉咙有";
        $topwords = str_replace(array('？',"，","！","怎","么","你","我","他",'的','了','和','呢','啊','哦','恩','嗯','吧'), "", $keyword);
        
        $key = $this->segment($keyword);
        //echo '<pre>';print_r($key); ;exit;
        $topwords = implode("|", $key);

        $cl->SetLimits(0, 8); //设置筛选的个数\
        $cl->SetMatchMode(SPH_MATCH_EXTENDED);
        $cl->SetSortMode(SPH_SORT_ATTR_DESC, "question_addtime"); //设置排序方式
        $resSphinx = $cl->Query( $topwords, "ask_question" );

        echo '<pre>';print_r($resSphinx);
    }

    public function actionreadCache()
    {
        $categorys = Category::model()->category_cache('categoryAll');
        echo '<pre>';print_r($categorys);exit;
    }

    //导入订单
    public function actionInsertOrders()
    {
         set_time_limit(0);
         $opt = isset($_REQUEST['opt']) ? $_REQUEST['opt'] : '';
         if( $opt == 's2' ){
            if ( $_FILES["xlsx"]["type"] == "application/vnd.ms-excel" ){
                $inputFileType = 'Excel5';
            }elseif ( $_FILES["xlsx"]["type"] == "application/octet-stream" || $_FILES["xlsx"]["type"] == "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" ){
                $inputFileType = 'Excel2007';
            }else {
                $this->message('上传文件类型有误');    
            }
            if ($_FILES["xlsx"]["error"] > 0) {
                $this->message('Error:'.$_FILES["xlsx"]["error"]);
            }
            if (empty($_FILES["xlsx"]["name"])) {
                $this->message('上传文件不存在!');
            }
            if ($_FILES["xlsx"]["size"] > 3 * 1024 * 1024 || $_FILES["xlsx"]['size'] == 0) {
                $this->message('上传文件大小不能超过3M且不能为0M!');
            }
            $filename = './upload/file/'.$_FILES["xlsx"]['name'];
            if( file_exists( $filename ) ){
                unlink($filename); //如果存在相同文件则删除
            }

            move_uploaded_file($_FILES["xlsx"]["tmp_name"], $filename);

            ini_set("memory_limit", "1024M");
            $PHPExcel = Fun::phpExcel();
            $objReader = PHPExcel_IOFactory::createReader($inputFileType);
            try{
                $objPHPExcel = $objReader->load($filename);
            }catch(Exception $e){
                $this->message('你太调皮了!');
            }
            
            $objWorksheet = $objPHPExcel->getActiveSheet();
            $highestRow = $objWorksheet->getHighestRow(); //总行数
            $highestColumn = $objWorksheet->getHighestColumn();
            $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);//总列数
            for ($row = 2;$row <= $highestRow;$row++){
                $data[$row-2]['name'] = $objWorksheet->getCellByColumnAndRow(0, $row)->getValue();
                $data[$row-2]['mobile'] = $objWorksheet->getCellByColumnAndRow(1, $row)->getValue();
                $data[$row-2]['state'] = $objWorksheet->getCellByColumnAndRow(2, $row)->getValue();
                $data[$row-2]['city'] = $objWorksheet->getCellByColumnAndRow(3, $row)->getValue();
                $data[$row-2]['county'] = $objWorksheet->getCellByColumnAndRow(4, $row)->getValue();
                $data[$row-2]['address_streetaddress'] = $objWorksheet->getCellByColumnAndRow(5, $row)->getValue();
            }
            $arr = count($data);
            if( $arr == 0 ){
                $this->message('上传文件的内容不能为空!');
            }
            $this->BuildOrder( $data );
            
            $this->message("订单导入成功!");
        }
        
        $this->render('tq');
    }

    /**
     * 生成订单
     */
    public function BuildOrder( $data ) {
        if( is_array($data) && count($data) ) {
            $now = date('Y-m-d H:i:s');
            $db = &Yii::app()->db;
            $product = ProductBasic::model()->findByPk(31588);
            foreach ($data as $ko => $vo) {
                $orderModel = new Orders();
                $log = new OrdersLog();//订单日志
                $orderModel->orders_sn = Fun::getOrdersSn();//订单号
                $orderModel->orders_buyerid = 726811;
                $orderModel->orders_status = 1;
                $orderModel->orders_paymentstatus = 1;
                $orderModel->orders_payment_trade_no = '1234567890';
                $orderModel->orders_payment_docno = 'ZKS123456789';
                $orderModel->orders_paymentstatus_time = $now;
                $orderModel->orders_invoicestatus = 1;
                $orderModel->orders_total_mktprice = $product->product_mktprice;
                $orderModel->orders_total_price = $product->product_purchasingprice;
                $orderModel->orders_total_allprice = $product->product_purchasingprice;

                $orderModel->orders_address_state_code =0;
                $orderModel->orders_address_city_code = 0;
                $orderModel->orders_address_county_code =0;

                $orderModel->orders_address_state = $vo['state'];
                $orderModel->orders_address_city = $vo['city'];
                $orderModel->orders_address_county = isset($vo['county'])?$vo['county']:"";

                $orderModel->orders_address_streetaddress = $vo['address_streetaddress'];
                $orderModel->orders_address_mobile = $vo['mobile'];
                $orderModel->orders_address_name = $vo['name'];
                $orderModel->orders_address_phone_countrycode = 'CN';

                $orderModel->orders_delivery_time_id = 1;
                $orderModel->orders_delivery = 1;
                $orderModel->orders_delivery_name = '圆通快递';

                $orderModel->orders_addtime = $now;
                $orderModel->orders_confirmtime = $now;
                $orderModel->orders_sourcetype = 1;
                $orderModel->orders_from_id = 1;
                $orderModel->orders_from = Yii::app()->params['OrdersFromId'][1];
                $orderModel->orders_isreplace = 0;
                $orderModel->orders_booking_from = 1;
                $orderModel->orders_booking_type = 0;
                $orderModel->orders_fromway = 13;
                $orderModel->orders_payway = 7;
                $orderModel->orders_payway_name ='支付宝';
                $orderModel->orders_nickname = '150000000000';
                if($orderModel->save()){
                    $log->saveLog($orderModel->orders_id,'buy/saveOrders',1,'订单导入欧姆龙');//订单创建
                }else{
                    $errors = $orderModel->getErrors();
                    $firstError = array_shift($errors);
                    $this->message($firstError['0']);
                }

                //订单详情
                $ogModel = new OrdersGoods();
                $ogModel->orders_goods_type = $product->product_typeid;
                $ogModel->orders_goods_ordersid = $orderModel->orders_id;
                $ogModel->orders_goods_parentid = 0;
                $ogModel->orders_goods_product_id = $product->product_id;
                $ogModel->orders_goods_product_supplierid = $product->product_supplierid;
                $ogModel->orders_goods_product_code = $product->product_code;
                $ogModel->orders_goods_product_cateid = $product->product_cateid;
                $ogModel->orders_goods_product_brandid = $product->product_brandid;
                $ogModel->orders_goods_product_name = $product->product_name;
                $ogModel->orders_goods_product_img = $product->product_img;
                $ogModel->orders_goods_product_price = $product->product_price;
                $ogModel->orders_goods_product_mktprice = $product->product_mktprice;
                $ogModel->orders_goods_product_maker = $product->product_maker;
                $ogModel->orders_goods_product_spec = $product->product_spec;
                $ogModel->orders_goods_product_purchasingprice = $product->product_purchasingprice;
                $ogModel->orders_goods_product_isfavor = $product->product_isfavor;
                $ogModel->orders_goods_amount = 1;
                $ogModel->orders_goods_isreplace = 0;
                if(!$ogModel->save()){
                    $this->message('创建订单遇到问题，请稍后再试');
                }else{
                    PastOrders::model()->writePost($orderModel->orders_id,0,$orderModel->orders_sn);//添加同步
                }
            }
        }
    }
    
    /**
     * 苏宁根据修改时间抓取订单测试
     * @return [type] [description]
     */
    public function actionTestZqOrder()
    {
        $db = & Yii::app()->db;
        $lasttime = $db->createCommand('SELECT value FROM {{config}} WHERE `module`=:module AND `key`=:key')->queryScalar(array(':module'=>"order",':key'=>"suning_lasttime"));
        echo $lasttime;exit;
        require_once ROOT_PATH.'/../protected/extensions/suning/SuningSdk.php';
        require_once ROOT_PATH.'/../protected/extensions/suning/DefaultSuningClient.php';
        $req = new OrdQueryRequest();

        $req->setStartTime('2016-04-18 00:00:01');
        $req->setEndTime('2016-04-19 00:00:01');
        $req->setOrderLineStatus('20');
        $req->setPageNo('1');
        $req->setPageSize('10');

        //api入参校验逻辑开关，当测试稳定之后建议设置为 false 或者删除该行
        $req -> setCheckParam('true');
        $serverUrl = SERVER_URL;
        $appKey = APP_KEY;
        $appSecret = APP_SECRET;
        $client = new DefaultSuningClient($serverUrl,$appKey,$appSecret,'json');
        $resp = $client -> execute($req);
        $data = json_decode($resp,true);
echo '<pre>';
        print_r($data);exit;
        print_r("返回响应报文:".$resp);
    }

     /**
     * 苏宁批量获取订单测试
     * @return [type] [description]
     */
    public function actionTestZqOrderByDay()
    {
        require_once ROOT_PATH.'/../protected/extensions/suning/SuningSdk.php';
        require_once ROOT_PATH.'/../protected/extensions/suning/DefaultSuningClient.php';
        $req = new OrderQueryRequest();

        $req->setStartTime('2016-03-20 00:00:01');
        $req->setEndTime('2016-04-19 00:00:01');
        $req->setOrderStatus("20");
        $req->setPageNo("3");
        $req->setPageSize("10");

        //api入参校验逻辑开关，当测试稳定之后建议设置为 false 或者删除该行
        $req -> setCheckParam('true');
        $serverUrl = SERVER_URL;
        $appKey = APP_KEY;
        $appSecret = APP_SECRET;
        $client = new DefaultSuningClient($serverUrl,$appKey,$appSecret,'json');
        $resp = $client -> execute($req);

        $data = json_decode($resp,true);
        echo '<pre>';print_r($data);exit;
        if( isset( $data['sn_responseContent']['sn_error'] ) ){
            echo $data['sn_responseContent']['sn_error']['error_code'];exit;
        }else{
            $return_msg = $data['sn_responseContent']['sn_head'];
            $return_data = $data['sn_responseContent']['sn_body']['orderQuery']; 
        }

        if( $return_msg['returnMessage'] == "biz.handler.data-get:success" ){
            $res = OrdersThird::model()->InsertOrders( $return_data, 1 );
        }
    }
    
    /**
     * 苏宁批量获取订单号
     * @return [type] [description]
     */
    public function actionTestZqOrderSn()
    {
        require_once ROOT_PATH.'/../protected/extensions/suning/SuningSdk.php';
        require_once ROOT_PATH.'/../protected/extensions/suning/DefaultSuningClient.php';
        $req = new OrdercodeQueryRequest();
        //赋值……
        $req->setStartTime('2016-03-16 00:00:01');
        $req->setEndTime('2016-04-15 00:00:01');
        $req->setOrderStatus('20');

        //api入参校验逻辑开关，当测试稳定之后建议设置为 false 或者删除该行
        $req -> setCheckParam('true');
        $serverUrl = SERVER_URL;
        $appKey = APP_KEY;
        $appSecret = APP_SECRET;
        $client = new DefaultSuningClient($serverUrl,$appKey,$appSecret,'json');
        $resp = $client -> execute($req);
        print_r("返回响应报文:".$resp);

    }

    /**
     * 苏宁单笔订单查询
     * @return [type] [description]
     */
    public function actionTestZqOrderSignle()
    {
        OrdersThird::model()->TestZqOrderSignle('1032805191');
    }

    /**
     * 苏宁订单发货
     * @return [type] [description]
     */
    public function actionTestSnFh()
    {
        require_once ROOT_PATH.'/../protected/extensions/suning/SuningSdk.php';
        require_once ROOT_PATH.'/../protected/extensions/suning/DefaultSuningClient.php';
        $req = new OrderselfdistAddRequest();
        $req->setOrderCode("111");
        $req->setDeliveryPerName("222");
        $req->setDeliveryPerPhone("333");
        $req->setDeliveryTime("4444");

        $req -> setCheckParam('true');
        $serverUrl = "http://opensit.cnsuning.com/api/http/sopRequest";
        $appKey = "88d4b0f16fda4d867391a45b4e9b66a2";
        $appSecret = "c15f3e6184caae59c0522b951721b0b6";
        $client = new DefaultSuningClient($serverUrl,$appKey,$appSecret,'json');
        $resp = $client -> execute($req);
        print_r("返回响应报文:".$resp);
    }

    /**
     * 苏宁订单发货
     * @return [type] [description]
     */
    public function actionTestDelivery()
    {
        $orders_sn = '1033319539';
        $dname = '1002-YD';
        $dcode = '3912080479225';
        $thirdFh = OrdersThird::model()->OrderFH($orders_sn, $dcode, $dname);
        var_dump($thirdFh);exit;
    }

    /**
     * 360查询3个月订单
     * @return [type] [description]
     */
    public function actionHaoyao() {
        Yii::import('application.extensions.360haoyao.Haoyao');
        $serverUrl = Yii::app()->params['haoyao']['url'];
        $appKey = Yii::app()->params['haoyao']['appKey'];
        $appSecret = Yii::app()->params['haoyao']['appSecret'];

        $haoyao = new Haoyao($serverUrl, $appKey, $appSecret);
        $haoyao->setType(1);
        $haoyao->sethaveCFY(1);
        $haoyao->setEndTime(date("Y-m-d H:i:s",time()));
        $haoyao->setStartTime(date("Y-m-d H:i:s",strtotime('-20 day')));
        $haoyao->setPageSize(100);

        $ret = $haoyao->execute();

        echo '<pre>';print_r($ret);exit;
    }

    /**
     * 查询单条订单记录
     * @return [type] [description]
     */
    public function actionHaoyao1() {
        Yii::import('application.extensions.360haoyao.Haoyao');
        $serverUrl = Yii::app()->params['haoyao']['url'];
        $appKey = Yii::app()->params['haoyao']['appKey'];
        $appSecret = Yii::app()->params['haoyao']['appSecret'];

        $haoyao = new Haoyao($serverUrl, $appKey, $appSecret);
        $haoyao->setType(3);
        $haoyao->sethaveCFY(1);
        $haoyao->setVenderId(11);
        $haoyao->setTid('A1383773499732497101');

        $ret = $haoyao->execute();

        echo '<pre>';print_r($ret);exit;
    }

    /**
     * 360查询一天内修改订单
     * @return [type] [description]
     */
    public function actionHaoyao2() {
        Yii::import('application.extensions.360haoyao.Haoyao');
        $serverUrl = Yii::app()->params['haoyao']['url'];
        $appKey = Yii::app()->params['haoyao']['appKey'];
        $appSecret = Yii::app()->params['haoyao']['appSecret'];

        $haoyao = new Haoyao($serverUrl, $appKey, $appSecret);
        $haoyao->setType(2);
        $haoyao->sethaveCFY(1);
        $haoyao->setEndModified(date("Y-m-d H:i:s",time()));
        $haoyao->setStartModified(date("Y-m-d H:i:s",strtotime('-1 day')));
        $haoyao->setPageSize(100);

        $ret = $haoyao->execute();

        echo '<pre>';print_r($ret);exit;
    }

    /**
     * 查询物流公司代码
     * @return [type] [description]
     */
    public function actionHaoyao3() {
        Yii::import('application.extensions.360haoyao.Haoyao');
        $serverUrl = Yii::app()->params['haoyao']['url'];
        $appKey = Yii::app()->params['haoyao']['appKey'];
        $appSecret = Yii::app()->params['haoyao']['appSecret'];

        $haoyao = new Haoyao($serverUrl, $appKey, $appSecret);
        $haoyao->setType(5);
        $haoyao->setLogistics(0);
        $ret = $haoyao->execute();

        echo '<pre>';print_r($ret);exit;
    }

    /**
     * 发货接口
     * @return [type] [description]
     */
    public function actionHaoyao4() {
        Yii::import('application.extensions.360haoyao.Haoyao');
        $serverUrl = Yii::app()->params['haoyao']['url'];
        $appKey = Yii::app()->params['haoyao']['appKey'];
        $appSecret = Yii::app()->params['haoyao']['appSecret'];

        $haoyao = new Haoyao($serverUrl, $appKey, $appSecret);
        $haoyao->setType(4);
        $haoyao->setTid('A1394265401958537101');
        $haoyao->setFH('881849770778948651','yuantong');
        $ret = $haoyao->execute();

        echo '<pre>';print_r($ret);exit;
    }

    public function actionHaoyao5()
    {
        $orders_sn = "A1854558773699687101";
        $dname = "1001-YTO";
        $dcode = "882203204111392196";
        $thirdFh = OrdersThird::model()->FhHaoyaoOrder($orders_sn, $dname, $dcode); //360健康城发货接口
        var_dump($thirdFh);exit;
        if( !$thirdFh['root']['success']) Yii::log('-----360健康城同步发货出错：'.$thirdFh['root']['msg'].'-----');
    }

    public function actionTestWeixn(){
echo '<pre>';
print_r( Yii::app()->cache->get('wXjsapi_ticket'));
echo '<br/>';
print_r( Yii::app()->cache->get('wXaccess_token'));

    }
	
	/**
     * 查询物流公司代码
     * @return [type] [description]
     */
    public function actionSouGou() {
        $tool=new Tool();
        $tool->sougou();
    }
	
	public function actionKeFu() {
       $this->render('kefu');
    }

    public function actionTestYYT() {
        Yii::import('application.extensions.yoyoto.StoreMaxInterface');
        $config = Yii::app()->params['StoreMax'];
        $hxm = 'E825571A';

        $storeMax = new StoreMaxInterface( $config );
        $storeMax->setVerificationCode($hxm);
        $storeMax->setType(1);
        $result = $storeMax->execute();
        echo '<pre>';
        print_r($result);
    }

    public function actionTestYYT2() {
        Yii::import('application.extensions.yoyoto.StoreMaxInterface');
        $config = Yii::app()->params['StoreMax'];
        $hxm = 'E825571A';

        $storeMax = new StoreMaxInterface( $config );
        $storeMax->setVerificationCode($hxm);
        $storeMax->setType(2);
        $result = $storeMax->execute();
        echo '<pre>';
        print_r($result);
    }

    public function actionTestaa() {
        $result = PromotionFavorBatch::model()->GetCoupon( 86, 0, 0 , false);
        echo '<pre>';print_r($result);exit;
    }

    /**
     * 测试OA接口
     * @return [type] [description]
     */
    public function actionTestOaApi()
    {
        $url = 'http://www.zkoat.com/api/jdShmFhOrderSyn';
        $partner = 'zkoa';
        $secret = 'zkoa123';
        $requestData = 
        array(
            '0' => array(
                'lsh' => '161129411723',
                'zfq' => date('Y-m-d H:i:s'),
                'rq' => date('Y-m-d H:i:s'),
                'goods' => array(
                    '0' =>array(
                        'hh' => 'ZKABT0018',
                        'sl' => 1,
                        'dj' => 1000.00,
                        'jshj' => 1000.00,
                    ),
                    '1' =>array(
                        'hh' => 'ZKABT0016',
                        'sl' => 3,
                        'dj' => 105.00,
                        'jshj' => 305.00,
                    )
                )
            ),
            '1' => array(
                'lsh' => '161129411726',
                'zfq' => date('Y-m-d H:i:s'),
                'rq' => date('Y-m-d H:i:s'),
                'goods' => array(
                    '0' =>array(
                        'hh' => 'ZKABT0015',
                        'sl' => 1,
                        'dj' => 1000.00,
                        'jshj' => 1000.00,
                    ),
                    '1' =>array(
                        'hh' => 'ZKABT0014',
                        'sl' => 3,
                        'dj' => 105.00,
                        'jshj' => 305.00,
                    )
                )
            )
        );

        $param = array();
        $param['partner'] = $partner;
        $param['time'] = strtotime(date('Y-m-d H:i:s'));
        $param['data'] = json_encode($requestData);
        ksort($param);
        $alldata='';
        $requestParam = '';
        foreach( $param as $k => $v ) {
            $alldata .= $k.$v;
            $requestParam .= $k.'='.$v.'&';
        }
        $sign = md5($alldata.$secret);
        $requestParam .= 'sign='.$sign;
        $res = $this->post($url, $requestParam);

        echo '<pre>';print_r(json_decode($res,true));exit;

    } 

    public function checkAilpay() {
        $res = Pay::model()->customs( 143536,'161214245954','2016121421001003430278720448', 19.90 );
        var_dump($res);exit;
    }

    public function actionFd() 
    {
        Orders::model()->orderSplit(1074286);
    }

    public function actionRctj()
    {
        Yii::import('application.extensions.rctj.Rctj');
        $Rctj = new Rctj();
        $p = $Rctj->getToken();
        var_dump($p);
    }

    public function actionsaleRctj()
    {
        Yii::import('application.extensions.rctj.Rctj');
        $Rctj = new Rctj();
        $Rctj->setCardType(119);
        $result = $Rctj->createVirtualCard();
        echo '<pre>'; print_r($result); 
    }

    public function actioncancelRctj()
    {
        Yii::import('application.extensions.rctj.Rctj');
        $Rctj = new Rctj();
        $Rctj->setCardNo('V10002369824');
        $result = $Rctj->CancelVirtualCard();
        echo '<pre>'; print_r($result);
    }

    public function actionlookRctj()
    {
        Yii::import('application.extensions.rctj.Rctj');
        $Rctj = new Rctj();
        $Rctj->setStartTime('2017-01-10');
        $Rctj->setEndTime('2017-01-10');
        $result = $Rctj->getUsedCardByTime();
        if( $result['code'] = 10000 ) {
            echo '<pre>'; print_r(json_decode($result['msg'],true));
        }
        
    }

    public function actionTestCookie()
    {
        $key = 'test';
        $src= 'aaaaaaa';
            $cookie = new CHttpCookie($key, $src);
            $cookie->domain = Yii::app()->params['cookie_domain'];
            Yii::app()->request->cookies[$key] = $cookie;


        //     $cookie = var_dump(Yii::app()->request->getCookies());
        // $shopcode = $cookie['mall']->value;
    }

    public function actionTestOrdersGoods()
    {
        $ogList = OrdersGoods::model()->getSynOrdersgoods(149778);
        echo '<pre>';
        print_r($ogList);exit;
    }

    public function actionrbktx()
    {
        $str = '150935,150936,151058,149417,149672,148702,150821,150823,148308,148448,148623,148624,148692,148693,148694,148695,148906,149220,149450,149501,149502,149528,149538,149677,149733,150009,150010,150111,150112,150265,150655,150674,150707,151011,151567,151638,151862,151864,152317,152442,152443,152469,151154,151427,149983,149675,149687,149690,151163,151166,151286,151626,151628,151630,151634,150283,149193,151608,151745,151749,148397,151502,149708';

        $orders = explode(',', $str);
        foreach( $orders as $k => $v ) {
            $order = Orders::model()->findByPk( $v );

            Orders::model()->commssionConfirm($order);
        }
        echo 'success';
        
    }


    public function actionGetSms()
    {
        $sms = new Sms();
        $sms->getSmsBalnce(1);
    }

    /**
     * 查询卡
     * @return [type] [description]
     */
    public function actionYfk()
    {
        $param = $_GET;
        Yii::import('application.extensions.yfk.Yfk');
        $yfk = new Yfk();
        $yfk->actionType = 1;
        $yfk->setParams('SaveCardNo',$param['a']);
        $yfk->setParams('PassWard',$param['b']);
        $result = $yfk->execute();
        Yii::log('=============>查询卡:'.print_r($result,true));
        var_dump($result);exit;
    }

    /**
     * 冻结卡
     * @return [type] [description]
     */
    public function actionYfkDj()
    {
        $param = $_GET;
        Yii::import('application.extensions.yfk.Yfk');
        $yfk = new Yfk();
        $yfk->actionType = 2;
        $yfk->setParams('SaveCardNo',$param['a']);
        $result = $yfk->execute();
        Yii::log('=============>冻结卡:'.print_r($result,true));
        var_dump($result);exit;
    }

    /**
     * 解冻卡
     * @return [type] [description]
     */
    public function actionYfkJd()
    {
        $param = $_GET;
        Yii::import('application.extensions.yfk.Yfk');
        $yfk = new Yfk();
        $yfk->actionType = 3;
        $yfk->setParams('SaveCardNo',$param['a']);
        $result = $yfk->execute();
        Yii::log('=============>解冻卡:'.print_r($result,true));
        var_dump($result);exit;
    }

    /**
     * 扣款接口
     * @return [type] [description]
     */
    public function actionYfkSave()
    {
        $param = $_GET;
        Yii::import('application.extensions.yfk.Yfk');
        $yfk = new Yfk();
        $yfk->actionType = 4;
        $payParams = array();
        $payOrder = array('OrderNo'=>'170203415996','SaveCardNo'=>'7001','PassWard'=>'111111','JSJE'=>floatval('100'),'BZ' => '单卡支付测试订单');
        // $payOrder1 = array('OrderNo'=>'170203415994','SaveCardNo'=>'M7558','PassWard'=>'123456','JSJE'=>floatval('50.00'),'BZ' => '单卡支付测试订单');
        // $payOrder2 = array('OrderNo'=>'170203415994','SaveCardNo'=>'M7559','PassWard'=>'123456','JSJE'=>floatval('25.00'),'BZ' => '单卡支付测试订单');
        array_push($payParams, $payOrder);
        // array_push($payParams, $payOrder1);
        // array_push($payParams, $payOrder2);
        $yfk->params['payOrRefoundParams'] = $payParams;
        $result = $yfk->execute();
        Yii::log('=============>扣款结果:'.print_r($result,true));
        var_dump($result);exit;
    }

    /**
     * 退款接口
     * @return [type] [description]
     */
    public function actionYfkRefound()
    {
        Yii::import('application.extensions.yfk.Yfk');
        $yfk = new Yfk();
        $yfk->actionType = 5;
        $refoundParams = array();
        $payOrder = array('DealBillNo'=>'6e6b0369-2398-4ab0-8de2-635d142fa4a8','Amount'=>'146.00','BZ'=>'测试退款');
        // $payOrder1 = array('DealBillNo'=>'51c20534-58b0-42c8-863b-cdebeb5b6fe7','Amount'=>'50','BZ'=>'测试退款');
        // $payOrder2 = array('DealBillNo'=>'7e268ae1-a1f6-487d-b2ab-3a16ae688ff3','Amount'=>'25','BZ'=>'测试退款');
        array_push($refoundParams, $payOrder);
        $yfk->params['payOrRefoundParams'] = $refoundParams;
        
        $result = $yfk->execute();
        Yii::log('=============>退款结果:'.print_r($result,true));
        var_dump($result);exit;
    }

    /**
     * 查询订单状态
     * @return [type] [description]
     */
    public function actionYfkStatus()
    {
        Yii::import('application.extensions.yfk.Yfk');
        $yfk = new Yfk();
        $yfk->actionType = 6;
        $yfk->lsh = '170302271181';
        $result = $yfk->execute();
        Yii::log('=============>状态查询:'.print_r($result,true));
        var_dump($result);exit;
    }

    /**
     * 预付卡充值接口
     * @return [type] [description]
     */
    public function actionYfkreCharge()
    {
        $param = $_GET;
        Yii::import('application.extensions.yfk.Yfk');
        $yfk = new Yfk();
        $yfk->actionType = 7;
        $yfk->setParams('SaveCardNo',$param['a']);
        $yfk->setParams('PassWard',$param['b']);
        $yfk->setParams('OrderNo','ZK12345'.rand(100000,999999));
        $yfk->setParams('CZJE','1000');
        $result = $yfk->execute();
        Yii::log('=============>卡充值:'.print_r($result,true));
        var_dump($result);exit;
    }

    public function actionDes()
    {
        Yii::import('application.extensions.yfk.Yfk');
        $yfk = new Yfk();
        $str = 'zheshi123354';
        $res1 = $yfk->DESEncrypt($str);
        echo '加密密文：'.$res1.'<br/>';
        $res2 = $yfk->DESDecrypt($res1);
        echo '解密密文：'.$res2.'<br/>';
    }

    public function actionSession()
    {echo '<pre>';
        print_r($_SESSION);

    }

    public function actionRedis()
    {
        $hash = new ARedisHash("myHashNameHere1",'redis');
        $hash->add('whatever',"someValue");
        $hash->add('greeting', "hello world");
        $hash->remove('greeting');
        $result = $hash->getData();
        echo '<pre>';print_r($result);exit;
    }

    public function actionTest() {
        $order = Orders::model()->findByPk(s);
        Orders::model()->orderSplit($order);
    }

    public function actionCash() {
        Yii::import('ext.aop.Cash');
        $cash = new Cash();
        $result = $cash->putCash('1122346782', 'xqigeh5879@sandbox.com', 2.30);
        var_dump($result);
    }

    public function actionGetCash() {
        Yii::import('ext.aop.Cash');
        $cash = new Cash();
        $result = $cash->FindCash('1122346782');
        var_dump($result);
    }

    public function actionaa(){
        //$res = CollectionLog::model()->getPvAndUvData('2017-05-08','2017-05-08', 0);
        $res = CollectionLog::model()->getFreeAndPayAnalyse('2017-06-01','2017-08-25');
        p($res);
    }


    public function actiontt() {
        Yii::import('application.extensions.ghxt.SelfRegister');
        $register = new SelfRegister();
        $register->params = array('hosCode'=>'32040700','isWithIntro'=>1);
        $data = $register->getHospital();
        p($data);exit;
    }


    public function actionTst() {
        $this->render('ttt');
    }

    public function actiona1() {
        if(!function_exists('fastcgi_finish_request')) {
          ob_end_flush();
          ob_start();
        }
        echo 'success';

        if(!function_exists('fastcgi_finish_request')) {
          header("Content-Type: text/html;charset=utf-8");
          header("Connection: close");
          header('Content-Length: '. ob_get_length());
          ob_flush();
          flush();
        } else {
          fastcgi_finish_request();
        }

        sleep(5);
    }


    public static function curlRequest($url, $post = '', $cookie = '', $returnCookie = 0)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)');
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
        curl_setopt($curl, CURLOPT_REFERER, "http://XXX");
        if ($post) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post));
        }
        if ($cookie) {
            curl_setopt($curl, CURLOPT_COOKIE, $cookie);
        }
        curl_setopt($curl, CURLOPT_HEADER, $returnCookie);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($curl);
        if (curl_errno($curl)) {
            return curl_error($curl);
        }
        curl_close($curl);
        if ($returnCookie) {
            list($header, $body) = explode("\r\n\r\n", $data, 2);
            preg_match_all("/Set-Cookie:([^;]*);/", $header, $matches);
            $info['cookie'] = substr($matches[1][0], 1);
            $info['content'] = $body;
            return $info;
        } else {
            return $data;
        }
    }


    //缓存医院
    public function actionGethos() {
        exit;
        $url = 'http://mhos.jiankang51.cn/support/get_data?pltId=03&productId=004&version=1.00.00&sessionId=8f5c2b466d9b4747a27e81e284a6c8dd&mName=searchHospitalInfoNew&pContent={"hosConfigFilter":"1"}';

        $data = self::curlRequest($url);
        $data = json_decode($data,true);
        $hospitals = $data['rspData']['body'];
        

        $tt = [];
        foreach( $hospitals as $ko => $vo ) {
            $tt[] = '("'.$vo['hospitalName'].'", "'.$vo['hospitalCode'].'", "'.$vo['areaCode'].'")';
        }

        $sql = 'INSERT INTO 12320_hospital (`name`,`code`,`areaCode`) VALUES '.implode(',',$tt);

        Yii::app()->db->createCommand($sql)->execute();

    }


    public function actionGetDep()
    {
        exit;
        set_time_limit(0);
        $hospital = Yii::app()->cache->get('12320Hos');
        $sql = 'INSERT INTO 12320_department (`hosId`,`name`,`code`,`speciality`) VALUES ';

        $db = &Yii::app()->db;
        foreach( $hospital as $ko => $vo ) {
            $url = 'http://mhos.jiankang51.cn/support/get_data?pltId=03&productId=004&version=1.00.00&sessionId=8f5c2b466d9b4747a27e81e284a6c8dd&mName=searchDeptmentInfoNew&pContent={"hospitalCode":"'.$vo[0].'"}';
            $data = json_decode(self::curlRequest($url),true);
            $departments = $data['rspData']['body'];
            $tt = [];
            foreach( $departments as $ka => $va ) {
                $tt[] = '("'.$vo[0].'","'.$va['departmentName'].'","'.$va['departmentId'].'","'.addslashes($va['speciality']).'")';
                
            }
            $db->createCommand($sql . implode(',',$tt))->execute();

        }

        echo 'success';

    }

    public function actionGetDoc()
    {
        set_time_limit(0);
        $db = &Yii::app()->db;
        $data = $db->createCommand('SELECT * from 12320_department')->queryAll();

        $sql = 'INSERT INTO 12320_doctor (`docId`,`depId`,`hosId`,`name`,`intro`,`good`,`title`) VALUES ';

        foreach( $data as $ko => $vo ) {
            $url='http://mhos.jiankang51.cn/support/get_data?pltId=03&productId=004&version=1.00.00&sessionId=8f5c2b466d9b4747a27e81e284a6c8dd&mName=searchSchedue&pContent={"hospitalCode":"'.$vo['hosId'].'","departmentId":"'.$vo['code'].'","queryType":"2"}';
            $data = json_decode(self::curlRequest($url),true);
            $doctors = $data['rspData']['expertBody'];
            $tt = [];
            foreach( $doctors as $ka => $va ) {
                $tt[] = '("'.$va['expertId'].'", "'.$vo['code'].'", "'.$vo['hosId'].'", "'.$va['expertName'].'", "'.addslashes($va['expertDesc']).'", "'.addslashes($va['expertSpeciality']).'","'.addslashes($va['expertTitle']).'")';
            }
            if( !empty($tt) && $tt ) $db->createCommand($sql . implode(',',$tt))->execute();
            
        }

        echo 'success';
    }

    public function actionWb()
    {
        //OrdersThird::model()->RequestWbOrder();
        Yii::import('application.extensions.wb.Wb');
        $wb = new Wb();
        // $wb->expressNumber = '123456789';
        // $wb->expressName = '韵达';
        // $wb->orderId = '14593';
        $data = $wb->getOrders();
        echo '<pre>';print_r($data);exit;

    }

    public function actionOi() {
        if( $_POST['sendMsg'] ) {
            if ( $_FILES["xlsx"]["type"] == "application/vnd.ms-excel" ){
                $inputFileType = 'Excel5';
            }elseif ( $_FILES["xlsx"]["type"] == "application/octet-stream" || $_FILES["xlsx"]["type"] == "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" ){
                $inputFileType = 'Excel2007';
            }else {
                $this->message('上传文件类型有误');    
            }
            if ($_FILES["xlsx"]["error"] > 0) {
                $this->message('Error:'.$_FILES["xlsx"]["error"], $this->createUrl('productbasic/import'));
            }
            if (empty($_FILES["xlsx"]["name"])) {
                $this->message('上传文件不存在!');
            }
            if ($_FILES["xlsx"]["size"] > 3 * 1024 * 1024 || $_FILES["xlsx"]['size'] == 0) {
                $this->message('上传文件大小不能超过3M且不能为0M!');
            }
            $filename = './upload/file/'.$_FILES["xlsx"]['name'];
            if( file_exists( $filename ) ){
                unlink($filename); //如果存在相同文件则删除
            }
            move_uploaded_file($_FILES["xlsx"]["tmp_name"], $filename);
            set_time_limit(0);
            $result = OrdersImport::model()->getImportData($inputFileType,$filename);
            if ( $result['code'] == 2 ) {
                echo $result['msg'];
            }
            exit;
        }

        $this->render('tq');
    }


//end class
}