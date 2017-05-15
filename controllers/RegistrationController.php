<?php

/**
 * Connected Communities Initiative
 * Copyright (C) 2016  Queensland University of Technology
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.org/licences GNU AGPL v3
 *
 */

namespace humhub\modules\registration\controllers;

use humhub\models\Setting;
use humhub\modules\evidence\models\StateRecord;
use humhub\modules\user\models\Profile;
use yii\db\Expression;
use yii\helpers\Url;
use Yii;
use humhub\components\Controller;
use humhub\modules\registration\models\ManageRegistration;
use yii\web\UploadedFile;

/**
 * @package humhub.modules_core.admin.controllers
 * @since 0.5
 */
class RegistrationController extends Controller
{
    public $subLayout = "@humhub/modules/admin/views/layouts/main";

    public function behaviors()
    {
        return [
            'acl' => [
                'class' => \humhub\components\behaviors\AccessControl::className(),
            ]
        ];
    }

    /**
     * @return array action filters
     */
    public function filters()
    {
        return array(
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
                'expression' => 'Yii::$app->user->isAdmin()'
            ),
            array('allow',
                'users' => array('@'),
                'actions' => array('updateUserTeacherType'),
            ),
            array('deny', // deny all users
                'users' => array('*'),
            ),
        );
    }

    public function actionIndex()
    {
        $model = [];
        $keys = array_keys(ManageRegistration::$type);
        $setting = [];
        foreach ($keys as $key) {
            $model[$key] = new ManageRegistration;
        }

        foreach (ManageRegistration::$type as $key => $value) {
            $setting[$key] = Setting::find()->andWhere(['value' => $value])->andWhere(['name' => 'type_manage'])->one();
        }

        if (\Yii::$app->request->isPost && !empty($_POST['ManageRegistration'])) {
            if ($_POST['ManageRegistration']['type'] == ManageRegistration::TYPE_SUBJECT_AREA) {
                if (!empty($_POST['ManageRegistration']['subjectarea']) && is_array($_POST['ManageRegistration']['subjectarea']) && !empty($_POST['ManageRegistration']['name'])) {
                    foreach ($_POST['ManageRegistration']['subjectarea'] as $select) {
                        if ($select == "other") {
                            $m_reg1 = new ManageRegistration;
                            $m_reg1->name = $select;
                            $m_reg1->default = ManageRegistration::DEFAULT_ADDED;
                            $m_reg1->type = ManageRegistration::TYPE_SUBJECT_AREA;
                            $m_reg1->depend = 0;
                            $m_reg1->save(false);

                            $m_reg = new ManageRegistration;
                            $m_reg->attributes = $_POST['ManageRegistration'];
                            $m_reg->default = ManageRegistration::DEFAULT_ADDED;
                            $m_reg->type = ManageRegistration::TYPE_SUBJECT_AREA;
                            $m_reg->depend = $m_reg1->getPrimaryKey();
                            $m_reg->save(false);
                        } else {
                            $searchTeacherType = ManageRegistration::find()->andWhere('name="' . $select . '" AND type=' . ManageRegistration::TYPE_TEACHER_TYPE)->one();
                            if (!empty($searchTeacherType) && empty(ManageRegistration::find()->andWhere('name="' . $_POST['ManageRegistration']['name'] . '"AND depend=' . $searchTeacherType->id . ' AND type=' . ManageRegistration::TYPE_SUBJECT_AREA)->one())) {
                                $m_reg = new ManageRegistration;
                                $m_reg->attributes = $_POST['ManageRegistration'];
                                $m_reg->default = ManageRegistration::DEFAULT_ADDED;
                                $m_reg->type = ManageRegistration::TYPE_SUBJECT_AREA;
                                $m_reg->depend = $searchTeacherType->id;
                                $m_reg->save(false);
                            }
                        }
                    }
                    return $this->redirect(Url::toRoute("/registration/registration/index"));
                } else {
                    if (empty($_POST['ManageRegistration']['name'])) {
                        $model[$_POST['ManageRegistration']['type']]->addError("name", "Enter name");
                    }

                    if (empty($_POST['ManageRegistration']['subjectarea'])) {
                        $model[$_POST['ManageRegistration']['type']]->addError("name", "Empty select data");
                    }
                }

            } else {
                if (strtolower($_POST['ManageRegistration']['name']) != "other") {
                    if ($_POST['ManageRegistration']['type'] == ManageRegistration::TYPE_TEACHER_LEVEL) {
                        $path = Yii::getAlias("@webroot") . "/uploads/file/";
                        $file = UploadedFile::getInstance($model[$_POST['ManageRegistration']['type']], 'file');
                        if (!empty($file)) {
                            $model[$_POST['ManageRegistration']['type']]->load(Yii::$app->request->post());
                            $model[$_POST['ManageRegistration']['type']]->file = $file;
                            $model[$_POST['ManageRegistration']['type']]->file_name = $model[$_POST['ManageRegistration']['type']]->file->name;
                            $model[$_POST['ManageRegistration']['type']]->file_path = $path;
                            $model[$_POST['ManageRegistration']['type']]->default = ManageRegistration::DEFAULT_ADDED;

                            $model[$_POST['ManageRegistration']['type']]->save();
                            if (!$model[$_POST['ManageRegistration']['type']]->hasErrors()) {
                                $model[$_POST['ManageRegistration']['type']]->file->saveAs($path . $model[$_POST['ManageRegistration']['type']]->file->name);
                                return $this->redirect(Url::toRoute("/registration/registration/index"));
                            }
                        } else {
                            $model[$_POST['ManageRegistration']['level']]->addError("file", "File not upload");
                        }
                    } else {
                        if (empty($_POST['ManageRegistration']['name'])) {
                            $model[$_POST['ManageRegistration']['level']]->addError("file", "Enter name");
                        } else {
                            $model[$_POST['ManageRegistration']['type']]->load(Yii::$app->request->post());
                            $model[$_POST['ManageRegistration']['type']]->save();
                            if (!$model[$_POST['ManageRegistration']['type']]->hasErrors()) {
                                return $this->redirect(Url::toRoute("/registration/registration/index"));
                            }
                        }
                    }
                } else {
                    $model[$_POST['ManageRegistration']['level']]->addError("name", "Not valid data");
                }
            }
        }
        $levels = ManageRegistration::find()->andWhere(['type' => ManageRegistration::TYPE_TEACHER_LEVEL])->andFilterWhere(['`default`' => ManageRegistration::DEFAULT_ADDED])->orderBy(['updated_at' => SORT_DESC])->all();
        $types = ManageRegistration::find()->andWhere(['type' => ManageRegistration::TYPE_TEACHER_TYPE])->andFilterWhere(['`default`' => ManageRegistration::DEFAULT_ADDED])->orderBy(['updated_at' => SORT_DESC])->all();
        $subjects = ManageRegistration::find()->andWhere(['type' => ManageRegistration::TYPE_SUBJECT_AREA])->andFilterWhere(['`default`' => ManageRegistration::DEFAULT_ADDED])->orderBy(['updated_at' => SORT_DESC])->groupBy('name')->all();
        $interests = ManageRegistration::find()->andWhere(['type' => ManageRegistration::TYPE_TEACHER_INTEREST])->andFilterWhere(['`default`' => ManageRegistration::DEFAULT_ADDED])->orderBy(['updated_at' => SORT_DESC])->all();

        return $this->render('index', [
            'levels' => $levels,
            'types' => $types,
            'subjects' => $subjects,
            'interests' => $interests,
            'model' => $model,
            'setting' => $setting,
        ]);
    }

    public function actionType($type)
    {
        $model = Setting::find()->andWhere(['name' => 'type_manage'])->andWhere(['value' => ManageRegistration::$type[$type]])->one();
        Setting::updateAll(['value_text' => !$model->value_text ? 1 : 0], 'name = "type_manage" AND value="' . ManageRegistration::$type[$type] . '"');
        $this->redirect($_SERVER['HTTP_REFERER']);
    }

    public function actionRequired($required)
    {
        $model = Setting::find()->andWhere(['name' => 'required_manage'])->andWhere(['value' => ManageRegistration::$type[$required]])->one();
        Setting::updateAll(['value_text' => !$model->value_text ? 1 : 0], 'name = "required_manage" AND value="' . ManageRegistration::$type[$required] . '"');
        $this->redirect($_SERVER['HTTP_REFERER']);
    }

    public function actionEdit()
    {
        if (isset($_POST['pk']) && isset($_POST['value'])) {
            $pk = $_POST['pk'];
            $value = $_POST['value'];

            ManageRegistration::updateAll(['name' => $value], 'id=' . $pk);
        } else {
            echo "Error of data editing";
        }
    }

    public function actionEditSubject()
    {
        if (isset($_POST['value'])) {
            $value = $_POST['value'];
            $lastValue = $_POST['name'];
            ManageRegistration::updateAll(['name' => $value], ' type=' . ManageRegistration::TYPE_SUBJECT_AREA . ' AND name="' . $lastValue . '"');
        } else {
            echo "Error of data editing";
        }
    }

    public function actionDelete($id)
    {
        ManageRegistration::findOne($id)->delete();
        $expression = new Expression('depend=' . $id);
        ManageRegistration::deleteAll($expression);
        $this->redirect($_SERVER['HTTP_REFERER']);
    }

    public function actionDeleteSubject($name)
    {
//        $selectSubject = CHtml::listData(ManageRegistration::model()->findAll('name="'. $name . '" AND type='. ManageRegistration::TYPE_SUBJECT_AREA), "depend", "depend");
        $expression = new Expression("name='" . $name . "' AND type=" . ManageRegistration::TYPE_SUBJECT_AREA);
        ManageRegistration::deleteAll($expression);
//        ManageRegistration::model()->deleteAll("id IN(". implode(",", $selectSubject) .") AND type=". ManageRegistration::TYPE_TEACHER_OTHER);
        $this->redirect($_SERVER['HTTP_REFERER']);
    }

    public function actionSort()
    {
        if (isset($_POST['data']) && isset($_POST['type'])) {
            $i = 0;
            foreach ($_POST['data'] as $item_id) {
                $i--;
                ManageRegistration::updateAll(array('updated_at' => time() + $i), ['type' => $_POST['type'], 'id' => $item_id]);
            }
        } else {
            echo "Erorr of data sorting";
        }
    }

    public function actionDeleteSubjectItem()
    {
        if (isset($_POST['dataId']) && isset($_POST['dataDepend'])) {
            ManageRegistration::find()->andWhere(['id' => $_POST['dataId']])->one()->delete();
            echo true;
        }

        echo false;
    }

    public function actionUpdateApsts()
    {
        if (!empty($_POST) && !empty($_FILES['ManageRegistration']['size']['apstsfile'])) {
            $idType = $_POST['apsts_id'];
            $path = Yii::getAlias("@webroot") . "/uploads/file/";
            $model = ManageRegistration::findOne($idType);
            $model->file = UploadedFile::getInstance($model, 'apstsfile');
            $model->file_name = $model->file->name;
            $model->file_path = $path;

            $model->save(false);
            if (!$model->hasErrors()) {
                echo 1;
            }
            $model->file->saveAs($path . $model->file->name);
        } else {
            echo 0;
        }
    }

    public function actionUpdateUserTeacherType()
    {
        if (isset($_POST['teachertype']) && isset($_POST['teacherTypeOther'])) {
            $type = !empty($_POST['teacherTypeOther']) ? $_POST['teacherTypeOther'] : $_POST['teachertype'];
            $profile = Profile::find()->andWhere(['user_id' => Yii::$app->user->id])->one();
            if (!empty($profile)) {
                $profile->teacher_type = $type;
                $profile->save();
            }

            $registration = ManageRegistration::find()->andWhere(['name' => $type])->andWhere(['`default`' => 0])->one();
            if (empty($registration)) {
                $reg = new ManageRegistration();
                $reg->name = $type;
                $reg->type = ManageRegistration::TYPE_TEACHER_TYPE;
                $reg->default = ManageRegistration::DEFAULT_DEFAULT;
                $reg->save(false);
            }

            StateRecord::saveStateTeacherType();
            return true;
        }

        return false;
    }
}
