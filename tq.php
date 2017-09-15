<style>
    input.mt {font-size: 13px;padding: 5px;}
    .importInfo {border: 1px solid #dcdcdc; padding: 5px;}
    .msgTitle{margin: 20px 0 10px 0;}
    .msgTitle span{font-size: 15px;font-weight: bold; color: #333;}
    .msgInfo{border: 1px solid #333;padding: 5px; min-height: 500px; overflow:auto;}
</style>
<div class="msgTitle">
    <span>请先选择导入EXCEL：</span>
</div>
<div class="importInfo">
    <form id="productForm" action="<?php echo $this->createUrl('test/oi'); ?>" method="post" enctype="multipart/form-data">
        <input name="sendMsg" value="1" type="hidden" />
        <div class="add_newaddress">
            <ul class="address_box">
                <li>
                    <label for="xlsx" >请选择上传文件 <span class="required">*</span></label>
                    <input name="xlsx" id="xlsx" type="file"/>
                    <input type="submit" value="上传文件并执行导入" class="mt">
                </li>
            </ul>
            <div class="clear"></div>
        </div>
    </form>
</div>
<div class="msgTitle">
    <span>订单生产信息：</span>
</div>
<div class ="msgInfo"></div>

<script>

</script>