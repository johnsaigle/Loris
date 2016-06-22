<!-- main table -->
<div class="row">
	{$headerTable}
</div>

<div class="panel panel-default">
	<div class="panel-heading" id="panel-main-heading">
		<p>Detailed Biospecimen Properties</p>
	</div> <!-- closing panel-heading div-->
	<div class="panel-body">
			<table class="table table-hover table-bordered header-info col-xs-12 dynamictable">
			<tr>
				<th class="col-xs-2 info">Biospecimen ID</th><td class="col-xs-2">{$info[0].biospecimen_id}</td>
				<th class="col-xs-2 info">Sample Type</th><td class="col-xs-2">{$info[0].sample_type}</td>
			<tr>
				<th class="col-xs-2 info">Candidate ID</th><td class="col-xs-2">{$info[0].CandID}</td>
				<th class="col-xs-2 info">External Number</th><td class="col-xs-2">{$info[0].external_number}</td>
			<tr>
				<th class="col-xs-2 info">Diagnosis</th><td class="col-xs-2"></b>{$info[0].diagnosis}</b></td>
				<th class="col-xs-2 info">Sample Quality</th><td class="col-xs-2">{$info[0].sample_quality}</td>
			</tr>
			<tr>
				<th class="col-xs-2 info">Quantity On Hand</th><td class="col-xs-2">{$info[0].quantity_on_hand}</td>
				<th class="col-xs-2 info">Quantity Units</th><td class="col-xs-2">{$info[0].quantity_units}</td>
			</tr>
			<tr>
				<th class="col-xs-2 info">Storage Status</th><td class="col-xs-2">{$info[0].storage_status}</td>
				<th class="col-xs-2 info">Date of Symptom Onset</th><td class="col-xs-2">{$info[0].date_symptom_onset}</td>
			</tr>
			<tr>
				<th class="col-xs-2 info">Date Collected</th><td class="col-xs-2">{$info[0].date_collected}</td>
				<th class="col-xs-2 info">Receipt Date</th><td class="col-xs-2">{$info[0].date_received}</td>
			</tr>
			<tr>
				<th class="col-xs-2 info">Preparation Date</th><td class="col-xs-2">{$info[0].date_preparation}</td>
				<th class="col-xs-2 info">SOP Employed</th><td class="col-xs-2">{$info[0].sop_employed}</td>
			</tr>
			<tr>
				<th class="col-xs-2 info">F/T Cycles</th><td class="col-xs-2">{$info[0].ft_cycles}</td>
				<th class="col-xs-2 info">Hemolysis Index</th><td class="col-xs-2">{$info[0].hemolysis_index}</td>
			</tr>
			<tr>
				<th class="col-xs-2 info">Concentration</th><td class="col-xs-2">{$info[0].concentration}</td>
				<th class="col-xs-2 info">DNA Ratio</th><td class="col-xs-2">{$info[0].ratio}</td>
			</tr>
			</table>
	</div> <!--closing panel-body -->
</div> <!--panel panel-default -->
<!--Tracking view panel-->
<div class="panel panel-default">
	<div class="panel-heading" id="panel-main-heading">
		<p>Tracking Information</p>
	</div> <!-- closing panel-heading div-->
	<div class="panel-body">
			<table class="table table-hover table-bordered header-info col-xs-12 dynamictable">
			<tr>
				<th class="col-xs-2 info">Storage Address</th><td class="col-xs-2">{$info[0].storage_address}</td>
				<th class="col-xs-2 info">Inventory Status</th><td class="col-xs-2">{$info[0].inventory_status}</td>
				</tr>
			</table>
	</div> <!--closing panel-body -->
</div> <!--panel panel-default -->
{if $has_permission}</form>{/if} 
