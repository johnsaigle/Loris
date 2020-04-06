{if $captcha_key}
<script src="https://www.google.com/recaptcha/api.js"></script>
{/if}
<div class="panel panel-default panel-center">
  <div class="panel-heading">
    <h3 class="panel-title">
        {$page_title}
    </h3>
  </div>
  <div class="panel-body">
      {if $success}
    <div class="success-message">
      <h1>Thank you!</h1>
      <p>Your request for an account has been received successfully.</p>
      <p>Please <a href='mailto:ukbiobank.neurohub@mcin.ca'>email your supporting documents</a> in order to complete the registration process.</p>
      <a href="/" class="btn btn-primary btn-block">
        Return to Login Page
      </a>
    </div>
      {else}
    <p class="text-center">
      Please fill in the form below to request a LORIS account.<br/>
      We will contact you once your account has been approved.
    </p>
    <form method="POST" name="form1" id="form1">
      <div class="form-group">
          {$form.firstname.html}
          {if $form.firstname.error}
            <span id="helpBlock" class="help-block">
              <b class="text-danger">{$form.firstname.error}</b>
            </span>
          {/if}
      </div>
      <div class="form-group">
          {$form.lastname.html}
          {if $form.lastname.error}
            <span id="helpBlock" class="help-block">
              <b class="text-danger">{$form.lastname.error}</b>
            </span>
          {/if}
      </div>
      <div class="form-group">
          {$form.from.html}
          {if $form.from.error}
            <span id="helpBlock" class="help-block">
              <b class="text-danger">{$form.from.error}</b>
            </span>
          {/if}
      </div>
      <div class="form-group">
          {$form.site.html}
          {if $form.site.error}
            <span id="helpBlock" class="help-block">
              <b class="text-danger">{$form.site.error}</b>
            </span>
          {/if}
      </div>
      <div class="form-group">
          {$form.examiner.html}
          {* checkbox's html method in LORISForm seems to automagically add the label *}
      </div>
      <div class="form-group">
          {$form.radiologist.html}
      </div>
      <div class="form-group">
          <h4>Consent Information</h4>
		<p>You must read the following forms in order to request an account.</p>
		<ul>
		<!-- <li><a href={$mta}>Material Transfer Agreement</a></li> -->
		<li>Material Transfer Agreement (not yet available)</li>
		<li><a href='?download=consent'>McGill User Consent Form</a></li>
		</ul>
          {$form.consent.html}
          {if $form.consent.error}
            <span id="helpBlock" class="help-block">
              <b class="text-danger">{$form.consent.error}</b>
            </span>
          {/if}
	<!-- The following lines are not used at this time but may be useful later.
		Design decisions are needed for how uploaded files will be processed.
		<h5>Supporting Documents</h5>
	<!--	<p><i>Please add the completed McGill User Consent Form as well as any required supporting documents <b>as a single file</b> and upload using the button below.</i></p>
        <!--  {$form.upload.html}
        <!--  {if $form.upload.error}
        <!--    <span id="helpBlock" class="help-block">
        <!--      <b class="text-danger">{$form.upload.error}</b>
        <!--    </span>
        <!--  {/if} -->

      </div>
        {if $captcha_key}
            {* Google reCaptcha *}
          <div class="form-group">
            <div class="g-recaptcha" data-sitekey="{$captcha_key}"></div>
            <span id="helpBlock" class="help-block">
              <b class="text-danger">{$error_message['captcha']}</b>
            </span>
          </div>
        {/if}
      <div class="form-group">
        <input type="submit" name="Submit" class="btn btn-primary btn-block"
               value="Request Account"/>
      </div>
      <div class="form-group">
        <a href="/">Back to login page</a>
      </div>
        {/if}
  </div>
</div>
