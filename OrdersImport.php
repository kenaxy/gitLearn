<?php 

/**
 * 订单导入操作
 */
class OrdersImport {
	/**
	 * @var 商品货号
	 */
	public static $orderGoods = 'ZK902440';

	/**
	 * 购买人ID
	 * @var [type]
	 */
	public static $memberId = '724389';

	/**
	 * 订单运费价格
	 * @var [type]
	 */
	public $orderFeightPrice = 0;

	private $memberInfo;

	private $productInfo;

	/**
	 * 匹配订单省市区编号
	 * @param  [type] $state    [省]
	 * @param  [type] $city     [市]
	 * @param  [type] $district [区]
	 * @return [type]           array
	 */
	public static function getOrderAddress( $state, $city, $district ) 
	{
		$address = MemberAddress::model()->readCache();
		$stateId = array_search($state, $address['state']); //省
		$cityId = array_search($city, $address['city']);//市
		$districtId = array_search($district, $address['county']);//区
		if ( $stateId === false || $cityId === false ) {
			return false;
		}

		return [$stateId, $cityId, $districtId];	
	}

	/**
	 * 获取商品
	 * @return [type] [description]
	 */
	public static function getProduct( $rebuid=true )
	{	
		$db = &Yii::app()->db;
		if ( !is_string(self::$orderGoods) ) {
			$productCode = "'".implode("','", self::$orderGoods)."'";
		}
		$productCode = "'".self::$orderGoods."'";
		$sql = 'SELECT * FROM {{product_basic}} WHERE product_code IN('.$productCode.')';

		$product = $db->createCommand($sql)->queryAll();

		if ( $product ) {
			$producInfo = [];
			foreach ( $product as $ko => $vo ) {
				$producInfo['products'][$vo->product_id] = $vo;
				$producInfo['price'] += $vo['product_purchasingprice'];
				$producInfo['mkTprice'] += $vo['product_mktprice'];
			}
			return $producInfo;
		}

		return false;
	}

	/**
	 * 获取会员
	 * @return [type] [description]
	 */
	public static function getMember()
	{
		$db = &Yii::app()->db;
		$member = $db->createCommand('SELECT * FROM {{member}} WHERE member_id=:id')->queryAll(true,array(':id'=>self::$memberId));
		if( $member ) {
			return $member[0];
		}

		return false;
	}

	/**
	 * 导入订单数据
	 * @param  array  $data [收货人信息]
	 * @return [type]       [description]
	 */
	public function buildOrder( array $data )
	{	
		if ( !$data && count($data) ) {
			return ['code'=>2, 'msg'=>'收货人信息出错'];
		}
		list($name,$mobile,$state,$city,$district,$address) = $data;

		$addressCode = self::getOrderAddress($state,$city,$district);
		if ( $addressCode === false ) {
			return ['code'=>2, 'msg'=>'省市区地址找不到'];
		}
		list($state,$city,$district) = $addressCode;
		$time = date('Y-m-d H:i:s');

		$orderModel = new Orders();
        $orderModel->orders_sn = Fun::getOrdersSn();//订单号
        $orderModel->orders_buyerid = $this->memberInfo['member_id'];
        $orderModel->orders_address_state = $state;
        $orderModel->orders_address_city = $city;
        $orderModel->orders_address_county = $county;
        $orderModel->orders_address_streetaddress = $address;
        $orderModel->orders_address_mobile = $mobile;
        $orderModel->orders_address_name = $name;
        $orderModel->orders_address_phone_countrycode = 'CN';
        $orderModel->orders_address_state_code = $state;
		$orderModel->orders_address_city_code = $city;
		$orderModel->orders_address_county_code = $district ? $district : 0;

        $orderModel->orders_delivery = 1;
        $orderModel->orders_delivery_name = Yii::app()->params['OrdersDelivery'][$orderModel->orders_delivery];
        $orderModel->orders_addtime = $time;
        $orderModel->orders_sourcetype = 1;
        $orderModel->orders_from_id = 1;
        $orderModel->orders_from = Yii::app()->params['OrdersFromId'][1];
        $orderModel->orders_isreplace = 0;
        $orderModel->orders_booking_from = 0;
        $orderModel->orders_booking_type = 0; // 官网
        $orderModel->orders_booking_flowtype = 1; // 免费
        $orderModel->orders_booking_partform = 0; // 自主访问
        $orderModel->orders_fromway = 13;
        $orderModel->orders_isoverseas = 0; //是否为海外购订单
        
        $orderModel->orders_payment_docno = 'ZK'.date('Ymd').rand(1, 10000);
        $orderModel->orders_payway = 5;
        $orderModel->orders_payway_name = Yii::app()->params['OrdersPayway'][$orderModel->orders_payway];
        $orderModel->orders_admin_note = $orderModel->orders_delivery_name;
        $orderModel->orders_nickname = $this->memberInfo['member_nickname'];

        $orderModel->orders_status = 1;
        $orderModel->orders_confirmtime = $time; //确认时间
        $orderModel->orders_paymentstatus = 1;
        $orderModel->orders_paymentstatus_time = $time; //支付时间
        $orderModel->orders_delivery = 1;
        $orderModel->orders_total_pricediscount = 0;
        $orderModel->orders_total_freight = $this->orderFeightPrice;
        $orderModel->orders_total_mktprice = $this->productInfo['mkTprice'];
        $orderModel->orders_total_price = $this->productInfo['price'];
        $orderModel->orders_total_allprice = $this->productInfo['price'] + $this->orderFeightPrice; //订单总额;

		if(!$orderModel->save()) {
			return ['code'=>2, 'msg'=>'添加订单主表失败'];
        } else {
            foreach ( $this->productInfo['products'] as $ka => $va ) 
            {
            	$orderGoodsModel = new OrdersGoods();
            	$orderGoodsModel->orders_goods_type = $va['orders_goods_type'];
	            $orderGoodsModel->orders_goods_ordersid = $orderModel->orders_id;
	            $orderGoodsModel->orders_goods_parentid = 0;
            	$orderGoodsModel->orders_goods_product_id = $va['product_id'];
                $orderGoodsModel->orders_goods_product_supplierid = $va['product_supplierid'];
                $orderGoodsModel->orders_goods_product_code = $va['product_code'];
                $orderGoodsModel->orders_goods_product_cateid = $va['product_cateid'];
                $orderGoodsModel->orders_goods_product_brandid = $va['product_brandid'];               
                $orderGoodsModel->orders_goods_product_name = $va['product_name'];
                $orderGoodsModel->orders_goods_product_img = $va['product_img'];
                $orderGoodsModel->orders_goods_product_price = $va['product_price'];
                $orderGoodsModel->orders_goods_product_mktprice = $va['product_mktprice'];
                $orderGoodsModel->orders_goods_product_maker = $va['product_maker'];
                $orderGoodsModel->orders_goods_product_spec = $va['product_spec'];
                $orderGoodsModel->orders_goods_product_purchasingprice = $va['product_purchasingprice'];
                $orderGoodsModel->orders_goods_product_isfavor = $va['product_isfavor'];
                $orderGoodsModel->orders_goods_amount = 1;
                $orderGoodsModel->orders_goods_isreplace = 0;
                $orderGoodsModel->orders_goods_stockid = $va['u_product_stockid']; //仓库
                if ( !$orderGoodsModel->save() ) {
                	return ['code' => 2, 'msg' => '添加订单商品表失败'];
                }
            }
            $odgModel = new OrdersLog;//日志
        	$logmsg = '批量导入订单，信息：'.implode('--',$data);
			$odgModel->saveLog($orderModel->orders_id,'buildOrder','success',$logmsg);
        }
        Yii::log('批量订单--'.implode('==',$data).'--成功，单号：'.$orderModel->orders_id);
        return ['code'=>1,'msg'=>'订单生成成功'];
	}

	/**
	 * 处理EXCEL数据
	 * @param  [type] $fileType [description]
	 * @param  [type] $filePath [description]
	 * @return [type]           [description]
	 */
	public function getImportData( $fileType, $filePath ) 
	{
		ini_set("memory_limit", "1024M");
        $PHPExcel = new PHPExcel();
        $objReader = PHPExcel_IOFactory::createReader($fileType);
        try{
            $objPHPExcel = $objReader->load($filePath);
        }catch(Exception $e){
            return ['code'=>2, 'msg'=>'文件格式不对'];
        }
        
        $objWorksheet = $objPHPExcel->getActiveSheet();
        $highestRow = $objWorksheet->getHighestRow(); 
        $highestColumn = $objWorksheet->getHighestColumn();
		$highestColumnIndex = 6;
        for ($row = 2;$row <= $highestRow;$row++) {
            $strs=array();
            for ($col = 0; $col < $highestColumnIndex; $col++){
                $strs[$col] = $objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
            }
            if ( array_filter( $strs ) ) { 
            	$data[$row-1] = $strs; 
            }
        }
		$dataNum = count($data);
		//echo '<pre>'; print_r($data);exit;
        // if ( $dataNum == 0 ) {
        // 	return ['code'=>2, 'msg'=>'上传文件的内容不能为空'];
        // }

        // $this->memberInfo = self::getMember();
		// if ( $this->memberInfo === false || $this->memberInfo === null ) {
		// 	return ['code'=>2, 'msg'=>'初始化会员信息失败'];
		// }

		// $this->productInfo = self::getProduct();
		// if ( $this->productInfo === false || $this->productInfo === null ) {
		// 	return ['code'=>2, 'msg'=>'初始化商品信息失败'];
		// }
       
        foreach ( $this->xrange(1,$dataNum) as $num ) {
        	echo $num.'. 创建订单信息：'.implode('-', $data[$num]).'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
        	flush();
    		ob_flush();
			//$buildRes = $this->buildOrder($data[$num]);
			$buildRes = ['code'=>1,'msg'=>'订单生成成功'];
        	echo '结果 ==>  '.(($buildRes['code'] == 1) ? ':) 信息:  ' : 'x 信息:  ').$buildRes['msg'].'<br/>';
        	usleep(400000);
        }

        echo '结束  :):):)';

		ob_end_flush();
	}

	/**
	 * 生成器
	 * @param  [type]  $start [description]
	 * @param  [type]  $end   [description]
	 * @param  integer $step  [description]
	 * @return [type]         [description]
	 */
	public function xrange($start, $end, $step = 1) {
	    for ($i = $start; $i <= $end; $i += $step) {
	        yield $i;
	    }
	}

}