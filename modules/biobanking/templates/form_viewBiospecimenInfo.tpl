<!-- main table -->
<div class="row">
	{$headerTable}
</div>

<div class="panel panel-default">
	<div class="panel-heading" id="panel-main-heading">
	</div> <!-- closing panel-heading div-->
<div class="panel-body">
<div class="col-xs-12 col-md-6 ib_frame">
	<div class="panel panel-default" id="subpanel-{$files[file].GenomicFileID}">
		<div class="panel-heading" id="mri-panel-{$files[file].GenomicFileID}">
		<h3 class="panel-title"> 
	{* <span class="pull-right clickable glyphicon arrow glyphicon-chevron-up" onclick="toggle_mriPanel('{$files[file].GenomicFileID}')"></span> *}
		<div class="pull-right">
		</div><!--cloding pull-right div -->
	</div> <!--closing panel-heading clickable -->
{*  </div> *}<!--closing row div -->
<div class="panel-body">
	<div class="mri-righttable col-xs-12" id="mri-righttable-{$info[0].biospecimen_id}">
		<table class="table table-hover table-bordered header-info col-xs-12 dynamictable">
		<tr>
			<th class="col-xs-2 info">Biospecimen ID</th><td class="col-xs-2">{$info[0].biospecimen_id}</td>
			<th class="col-xs-2 info">Sample Type</th><td class="col-xs-2">{$info[0].sample_type}</td>
		<tr>
			<th class="col-xs-2 info">Candidate ID</th><td class="col-xs-2">{$info[0].CandID}</td>
			<th class="col-xs-2 info">Date Collected</th><td class="col-xs-2">{$info[0].date_collected}</td>
		<tr>
			<th class="col-xs-2 info">Date Registered</th><td class="col-xs-2"></b>{$info[0].date_registered}</b></td>
			<th class="col-xs-2 info">Expiration Date</th><td class="col-xs-2">{$info[0].expiration_date}</td>
		</tr>
		<tr>
			<th class="col-xs-2 info">Storage Location</th><td class="col-xs-2">{$info[0].storage_location}</td>
			<th class="col-xs-2 info">Volume (mLs)</th><td class="col-xs-2">{$info[0].volume_mls}</td>
		</tr>
		<tr>
			<th class="col-xs-2 info">Date Last Processed</th><td class="col-xs-2">{$info[0].date_last_processed}</td>
			<th class="col-xs-2 info">Last Processed By</th><td class="col-xs-2">{$info[0].last_processed_by}</td>
		</tr>
		<tr>
			<th class="col-xs-2 info">Methodology</th><td class="col-xs-2">{$info[0].methodology}</td>
			<th class="col-xs-2 info">Date of Last Import</th><td class="col-xs-2">{$info[0].date_of_last_import}</td>
		</tr>
		<tr>
			<th class="col-xs-2 info">Study</th><td class="col-xs-2">{$info[0].study}</td>
			<th class="col-xs-2 info">Parent ID</th><td class="col-xs-2">{$info[0].parent_id}</td>
		</tr>
		<tr>
			<th class="col-xs-2 info">Status</th><td class="col-xs-2">{$info[0].status}</td>
			<th class="col-xs-2 info">Cell Type</th><td class="col-xs-2">{$info[0].cell_type}</td>
		</tr>
	</table>
</div><!--closing mri-righttable -->
</div> <!--closing panel-body -->
</div> <!--panel panel-default -->
</div> <!--closing ib_frame div-->
</div> <!-- closing panel-body div-->
</div>
</div>
{if $has_permission}</form>{/if} 
