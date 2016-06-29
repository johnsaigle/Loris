<!-- TODO: move this into javascript files? -->
{literal}
<script type="text/javascript">
    $(document).ready(function(){
        $("#cand").DynamicTable({ "freezeColumn" : "pscid" });
    });
</script>
{/literal}
<script type="text/javascript" src="{$baseurl}/js/advancedMenu.js"></script>

<div class="row">
<div class="col-sm-12">
<div class="panel panel-primary">
    <div class="panel-heading" onclick="hideFilter(this)">
        Selection Filter 
        <!--Note: this label is currently hidden (display:none) -->
        <label class="advancedOptions" id="advanced-label" style="display:none">(Advanced Options)</label>
        <span class="glyphicon arrow glyphicon-chevron-up pull-right"></span>
    </div>
    <div class="panel-body">
    <!-- TODO: change this to 'pretty URL' scheme -->
        <form method="post" action="{$baseurl}/main.php?test_name=biobanking">
            <div class="row">
                <div class="form-group col-sm-4">
                    <label class="col-sm-12 col-md-4">
                        {$form.PSCID.label}
                    </label>
                    <div class="col-sm-12 col-md-8">
                        {$form.PSCID.html}
                    </div>
                </div>
                <div class="form-group col-sm-4">
                    <label class="col-sm-12 col-md-4">
                        {$form.DCCID.label}
                    </label>
                    <div class="col-sm-12 col-md-8">
                        {$form.DCCID.html}
                    </div>
                </div>
                <div class="form-group col-sm-4">
                    <label class="col-sm-12 col-md-4">
                        {$form.sample_type.label}
                    </label>
                    <div class="col-sm-12 col-md-8">
                        {$form.sample_type.html}
                    </div>
                </div>
            </div>
            <div class="row">
                {if count($form.centerID.options) > 1}
                <div class="form-group col-sm-4">
                    <label class="col-sm-12 col-md-4">
                        {$form.centerID.label}
                    </label>
                    <div class="col-sm-12 col-md-8">
                        {$form.centerID.html}
                    </div>
                </div>
                {/if}
                <div class="form-group col-sm-4">
                    <label class="col-sm-12 col-md-4">
                        {$form.biospecimen_id.label}
                    </label>
                    <div class="col-sm-12 col-md-8">
                        {$form.biospecimen_id.html}
                    </div>
                </div>
                <div class="form-group col-sm-4">
                    <label class="col-sm-12 col-md-4">
                    {$form.diagnosis.label}
                    </label>
                    <div class="col-sm-12 col-md-8">
                        {$form.diagnosis.html}
                    </div>
                </div>
            </div> <!-- End second row -->
            <div class="row">
                <div class="form-group col-sm-4">
                    <label class="col-sm-12 col-md-4">
                        {$form.sample_quality.label}
                    </label>
                    <div class="col-sm-12 col-md-8">
                        {$form.sample_quality.html}
                    </div>
                </div>
                <div class="form-group col-sm-4">
                    <label class="col-sm-12 col-md-4">
                        {$form.quantity_on_hand.label}
                    </label>
                    <div class="col-sm-12 col-md-8">
                        {$form.quantity_on_hand.html}
                    </div>
                </div>
                <div class="form-group col-sm-4">
                    <label class="col-sm-12 col-md-4">
                    {$form.storage_status.label}
                    </label>
                    <div class="col-sm-12 col-md-8">
                        {$form.storage_status.html}
                    </div>
                </div>
            </div> <!-- End third row -->
            <!-- Note: this is currently hidden -->
            <div class="advancedOptions" id="advanced-options" style="display:none">
                <div class="row">
                    <div class="form-group col-sm-4">
                       <label class="col-sm-12 col-md-4">
                        {$form.parent_id.label}
                    </label>
                    <div class="col-sm-12 col-md-8">
                        {$form.parent_id.html}
                    </div>
                  </div>
                    <div class="form-group col-sm-4">
                        <label class="col-sm-12 col-md-4">
                            {$form.Participant_Status.label}
                        </label>
                        <div class="col-sm-12 col-md-8">
                            {$form.Participant_Status.html}
                        </div>
                    </div>
                    <div class="form-group col-sm-4">
                        <label class="col-sm-12 col-md-4">
                            {$form.exp_date.label}
                        </label>
                        <div class="col-sm-12 col-md-8">
                            {$form.exp_date.html}
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-sm-4">
                        <label class="col-sm-12 col-md-4">
                            {$form.gender.label}
                        </label>
                        <div class="col-sm-12 col-md-8">
                            {$form.gender.html}
                        </div>
                    </div>
                    <div class="form-group col-sm-4">
                        <label class="col-sm-12 col-md-4">
                            {$form.Visit_Count.label}
                        </label>
                        <div class="col-sm-12 col-md-8">
                            {$form.Visit_Count.html}
                        </div>
                    </div>
                    <div class="form-group col-sm-4">
                        <label class="col-sm-12 col-md-4">
                            {$form.edc.label}
                        </label>
                        <div class="col-sm-12 col-md-8">
                            {$form.edc.html}
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-sm-4">
                        <label class="col-sm-12 col-md-4">
                            {$form.Latest_Visit_Status.label}
                        </label>
                        <div class="col-sm-12 col-md-8">
                            {$form.Latest_Visit_Status.html}
                        </div>
                    </div>
                    <div class="form-group col-sm-4">
                        <label class="col-sm-12 col-md-4">
                            &nbsp;
                        </label>
                        <div class="col-sm-12 col-md-8">
                            &nbsp;
                        </div>
                    </div>
                    <div class="form-group col-sm-4">
                        <label class="col-sm-12 col-md-4">
                            {$form.Feedback.label}
                        </label>
                        <div class="col-sm-12 col-md-8">
                            {$form.Feedback.html}
                        </div>
                    </div>
                </div>
            </div>
            <br class="visible-xs">
            <div id="advanced-buttons">
                <!-- <div class="form-group col-sm-6 col-sm-offset-6"> -->
                        <!-- <div class="col-sm-6"> -->
                            <div class="col-sm-4 col-md-3 col-xs-12 col-md-offset-3">
                                <input type="submit" name="filter" value="Apply Filters" id="showdata_advanced_options" class="btn btn-sm btn-primary col-xs-12" />
                            </div>

                            <div class="visible-xs col-xs-12"> </div>
                            <div class="visible-xs col-xs-12"> </div>
                            <div class="visible-xs col-xs-12"> </div>
                            <div class="visible-xs col-xs-12"> </div>
                            <div class="col-sm-4 col-md-3 col-xs-12">
                                <input type="button" name="reset" value="Clear Active Filters" class="btn btn-sm btn-primary col-xs-12" onclick="location.href='{$baseurl}/main.php?test_name=biobanking&reset=true'" />
                            </div>
                            <div class="visible-xs col-xs-12"> </div>
                            <div class="visible-xs col-xs-12"> </div>
                            <div class="visible-xs col-xs-12"> </div>
                            <div class="visible-xs col-xs-12"> </div>
                        <!-- </div> -->
                    <!-- </div> -->
            </div>
        </form>
    </div>
</div>
</div>
<!-- TODO: Is this necessary? Should be biobanking always....?? -->
{if not $biobanking}
<div class="col-sm-3">
    <div class="panel panel-primary">
    <div class="panel-heading" onclick="hideFilter(this)">
        Open Profile
        <span class="glyphicon arrow glyphicon-chevron-up pull-right"></span>
    </div>
    <div class="panel-body" id="panel-body">
    <form class="form-horizontal" id="biobankingForm" name="biobankingForm" method="get" action="main.php">
        <input type="hidden" name="test_name" value="timepoint_list">
        <div class="form-group col-sm-12">
            <label class="col-sm-12 col-md-4">
                {$form.candID.label}
            </label>
            <div class="col-sm-12 col-md-8">
                {$form.candID.html}
            </div>
        </div>
        <div class="form-group col-sm-12">
            <label class="col-sm-12 col-md-4">
                 {$form.PSCID.label}
            </label>
            <div class="col-sm-12 col-md-8">
                 {$form.PSCID.html}
            </div>
        </div>
        <input type="submit" value="Open Profile" class="btn btn-sm btn-primary col-md-5 col-sm-12 col-md-offset-5">
    </form>
   </div>
</div> <!--closing col-sm-3 tag -->
</div>
{/if}
</div>
<div class="row">
<table border="0" valign="bottom" width="100%">
<tr>
    <!-- title -->
    <td class="controlPanelSection">
        {$numCandidates} subject(s) selected. 
        <a href="{$csvUrl}" download="{$csvFile}.csv">
            Download as CSV
        </a>
    </td>
    <!-- display pagination links -->
    <td align="right">{$page_links}</td>
</tr>
</table>
<!-- TODO: wrap this in a row (?) -->
<table id="cand" class="table table-hover table-primary table-bordered colm-freeze" border="0" width="100%">
    <thead>
        <tr class="info">
         <th id="number">No.</th>
            <!-- print out column headings - quick & dirty hack -->
            {section name=header loop=$headers}
                {if $headers[header].displayName == 'PSCID'}
                    <th id="pscid">
                {else}
                    <th>
                {/if}
                <!-- TODO: change to pretty URL -->
                <a href="{$baseurl}/main.php?test_name=biobanking&filter[order][field]={$headers[header].name}&filter[order][fieldOrder]={$headers[header].fieldOrder}">{$headers[header].displayName}</a></th>
            {/section}
        </tr>
    </thead>
    <tbody>
        {section name=item loop=$items}
            <tr>
            <!-- print out data rows -->
            {section name=piece loop=$items[item]}
                {if $items[item][piece].bgcolor != ''}
                    <td style="background-color:{$items[item][piece].bgcolor}">
                {else}
                    <td>
                {/if}
                {if $items[item][piece].DCCID != "" AND $items[item][piece].name == "PSCID"}
                    {assign var="PSCID" value=$items[item][piece].value}
                    <!-- TODO: change to pretty URL -->
                    <a href="{$baseurl}/main.php?test_name=timepoint_list&candID={$items[item][piece].DCCID}">{$items[item][piece].value}</a>
                {elseif $items[item][piece].name == "biospecimen_id"}
                        <!-- TODO: change to pretty URL -->
                        <a class="biospecimenLink" data-pscid="{$PSCID}" href="{$baseurl}/main.php?test_name=biobanking&subtest=viewBiospecimenInfo&biospecimen_id={$items[item][piece].value}">{$items[item][piece].value}</a>
                {else}
                    {$items[item][piece].value}
                {/if}
                </td>
            {/section}
            </tr>
        {sectionelse}
            <tr><td colspan="12">No specimens found</td></tr>
        {/section}
    </tbody>
<!-- end data table -->
</table>
