<div class="form">
    <h1>Password Reset</h1>

    <?php if (Yii::app()->user->hasFlash('success')) { ?>
        <div class="info">
            <?php echo Yii::app()->user->getFlash('success'); ?>
        </div>
    <?php } ?>


    <p class="note">Fields with <span class="required">*</span> are required.</p>
    <?php echo CHtml::beginForm(); ?>
    <?php
    if (isset($errors) && !empty($errors)) {
        foreach ($errors as $error) {
            ?>
            <div class="error"><?php echo $error; ?></div>
            <?php
        }
    }
    ?>

    <?php echo CHtml::errorSummary($form); ?>
    <div class="row">
        <?php echo CHtml::activeLabelEx($form, 'password'); ?>
        <?php echo CHtml::activePasswordField($form, 'password'); ?>
    </div>
    <div class="row">
        <?php echo CHtml::activeLabelEx($form, 'confirm_password'); ?>
        <?php echo CHtml::activePasswordField($form, 'confirm_password'); ?>
    </div>
    <div class="row">
        <?php if (CCaptcha::checkRequirements()) { ?>
            <?php echo CHtml::activeLabelEx($form, 'verify_code'); ?>
            <?php $this->widget('CCaptcha'); ?>
            <?php echo CHtml::activeTextField($form, 'verify_code'); ?>
            <?php echo CHtml::error($form, 'verify_code'); ?>
        <?php } ?>
    </div>
    <?php echo CHtml::submitButton('Submit'); ?>
    <?php echo CHtml::endForm(); ?>
</div>
<style>
    div.form {
        width: 380px;
        margin: 10px auto;
        background: white;
        padding: 10px;
        border-radius: 10px;
        box-shadow: 2px 2px 10px grey;
    }
</style>
