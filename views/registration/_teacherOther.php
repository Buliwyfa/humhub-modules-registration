<?php 
$flag = HSetting::model()->find('name = "type_manage" AND value="'.ManageRegistration::$type[ManageRegistration::TYPE_TEACHER_OTHER]. '"')->value_text;
$required = HSetting::model()->find('name = "required_manage" AND value="'.ManageRegistration::$type[ManageRegistration::TYPE_TEACHER_OTHER]. '"')->value_text;
?>
<h4>Teacher Type Other</h4>

<div class="table-responsive">
	<div class="row no-margin">
    	<div class="col-xs-6 no-padding">
			<h5><strong>Item Name</strong></h5>                     
    	</div>
        <div class="col-xs-6 no-padding">
            <div class="checkbox regular-checkbox-container pull-right checkbox-required">
                <label>
                    <a href='<?= $this->createUrl('required', ['required' => ManageRegistration::TYPE_TEACHER_OTHER]) ?>' data-method='post'>
                        <input class="regular-checkbox" type='checkbox' value="checkbox-required-teachertype" <?= $required?"checked":"" ?>/> required field
                        <div class="regular-checkbox-box"></div>
                    </a>
                </label>
                <div class="regular-checkbox-clear"></div>
            </div>
      	</div>
    </div>
    <div class="table-scrollable">
        <table class="table table-hover">    
            <tbody class='c_items' data-type="<?= ManageRegistration::TYPE_TEACHER_OTHER ?>">
				<?php
                if (empty($others)) {
                    echo '<tr><td class="empty"><span class="empty">Add items to the list.</span></td></tr>';
                } else {
                    foreach ($others as $type) {
                        echo '<tr class="ui-sortable" data-item="item_'.$type->id.'"><td style="z-index:99999;"><i class="fa fa-bars dragdrop"></i><span class="m_item" data-pk="' . $type->id . '" data-url="' . $this->createUrl('edit') . '">'.$type->name.'</span></td><td><a class="btn btn-danger btn-xs tt close" title="delete" href="' . $this->createUrl('delete', ['id' => $type->id]) . '"><i class="fa fa-times"></i></a></td></tr>';
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

          
<div class="form form-registration-items">
    <?php echo CHtml::beginForm(['action' => '', 'method' => 'post']); ?>
    <?php echo CHtml::activeHiddenField($model[ManageRegistration::TYPE_TEACHER_OTHER], 'type', ['value' => ManageRegistration::TYPE_TEACHER_OTHER]); ?>
    <?php echo CHtml::activeHiddenField($model[ManageRegistration::TYPE_TEACHER_OTHER], 'default', ['value' => ManageRegistration::DEFAULT_ADDED]); ?>
    <div class="row controls">
        <div class="col-xs-12">
                <?php echo CHtml::errorSummary($model[ManageRegistration::TYPE_TEACHER_OTHER], '', ''); ?>
        </div>
        <div class="col-sm-6">
            <?php echo CHtml::activeTextField($model[ManageRegistration::TYPE_TEACHER_OTHER], 'name', array('class' => 'form-control input-sm', 'placeholder' => 'Enter item name',)); ?>
            <button type="submit" name="btn" class="btn btn-primary btn-sm">
                <i class="fa fa-plus"></i> add item
            </button>
        </div>

        <div class="col-sm-6">
        	<div class="checkbox regular-checkbox-container pull-right">
                <label>
                    <a href='<?= $this->createUrl('type', ['type' => ManageRegistration::TYPE_TEACHER_OTHER]) ?>' data-method='post'>
                        <input class="regular-checkbox" type='checkbox' <?= $flag?"checked":"" ?>/> add 'other' option to list
                        <div class="regular-checkbox-box"></div>
                    </a>
                </label>
                <div class="regular-checkbox-clear"></div>
            </div>
        </div>
    </div>
	<?php echo CHtml::endForm(); ?>
</div>

<hr class="hr-spacer">

