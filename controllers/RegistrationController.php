<?php

/**
 * @package humhub.modules_core.admin.controllers
 * @since 0.5
 */
class RegistrationController extends Controller
{
    public $subLayout = "application.modules_core.admin.views._layout";

    /**
     * @return array action filters
     */
    public function filters()
    {
        return array (
            'accessControl', // perform access control for CRUD operations
        );
    }
    
    
    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules()
    {
        return array(
            array('allow',
                'expression' => 'Yii::app()->user->isAdmin()'
            ),
            array('deny', // deny all users
                'users' => array('*'),
            ),
        );
    }

    public function actionIndex()
    {
        $model  = [];
        $keys = array_keys(ManageRegistration::$type);
        $setting = [];
        foreach ($keys as $key) {
            $model[$key] = new ManageRegistration;
        }
        
        foreach(ManageRegistration::$type as $key => $value) {
            $setting[$key] = HSetting::model()->find('value="' . $value . '"');
        }
        
        if (\Yii::app()->request->isPostRequest) {
            $model[$_POST['ManageRegistration']['type']]->attributes = $_POST['ManageRegistration'];
            $model[$_POST['ManageRegistration']['type']]->save();
        }
        
        $levels = ManageRegistration::model()->findAll('type = ' . ManageRegistration::TYPE_TEACHER_LEVEL . (!($setting[ManageRegistration::TYPE_TEACHER_LEVEL]->value_text)?' AND `default`='. ManageRegistration::DEFAULT_ADDED:"") . " ORDER BY updated_at DESC");
        $types = ManageRegistration::model()->findAll('type = ' . ManageRegistration::TYPE_TEACHER_TYPE . (!$setting[ManageRegistration::TYPE_TEACHER_TYPE]->value_text?' AND `default`='. ManageRegistration::DEFAULT_ADDED:"") . " ORDER BY updated_at DESC");
        $subjects = ManageRegistration::model()->findAll('type = ' . ManageRegistration::TYPE_SUBJECT_AREA . (!$setting[ManageRegistration::TYPE_SUBJECT_AREA]->value_text?' AND `default`='. ManageRegistration::DEFAULT_ADDED:"") . " ORDER BY updated_at DESC");
        $interests = ManageRegistration::model()->findAll('type = ' . ManageRegistration::TYPE_TEACHER_INTEREST . (!$setting[ManageRegistration::TYPE_TEACHER_INTEREST]->value_text?' AND `default`='. ManageRegistration::DEFAULT_ADDED:"") . " ORDER BY updated_at DESC");

        $this->render('index', [
            'levels' => $levels,
            'types' => $types,
            'subjects' => $subjects,
            'interests' => $interests,
            'model' => $model,
        ]);
    }

    public function actionType($type)
    {
        $model = HSetting::model()->find('value="'.ManageRegistration::$type[$type]. '"');
        HSetting::model()->updateAll(['value_text' => !$model->value_text?1:0], 'value="'.ManageRegistration::$type[$type]. '"');
        $this->redirect($_SERVER['HTTP_REFERER']);
    }
    
    public function actionEdit()
    {
        if (isset($_POST['pk']) && isset($_POST['value'])) {
            $pk = $_POST['pk'];
            $value = $_POST['value'];

            ManageRegistration::model()->updateAll(['name' => $value], 'id=' . $pk);
        } else {
            echo "Erorr of data editing";
        }
    }
    
    public function actionDelete($id)
    {
        ManageRegistration::model()->deleteByPk($id);
        $this->redirect($_SERVER['HTTP_REFERER']);
    }
    
    public function actionSort()
    {
        if (isset($_POST['data']) && isset($_POST['type'])) {
            $i=0;
            foreach ($_POST['data'] as $item_id) {
                $i--;
                $criteria = new CDbCriteria;
                $criteria->addCondition('type='. $_POST['type']);
                $criteria->addCondition('id='. $item_id);
                ManageRegistration::model()->updateAll(array('updated_at' => time() + $i), $criteria);
            }
        } else {
            echo "Erorr of data sorting";
        }
    }
}
