!function(modules){function __webpack_require__(moduleId){if(installedModules[moduleId])return installedModules[moduleId].exports;var module=installedModules[moduleId]={exports:{},id:moduleId,loaded:!1};return modules[moduleId].call(module.exports,module,module.exports,__webpack_require__),module.loaded=!0,module.exports}var installedModules={};return __webpack_require__.m=modules,__webpack_require__.c=installedModules,__webpack_require__.p="",__webpack_require__(0)}([function(module,exports){"use strict";Object.defineProperty(exports,"__esModule",{value:!0});/**
	 *  The following file contains the base component for the data query react app.
	 *  It also contains the component for the saved queries dropdown.
	 *
	 *  @author   Jordan Stirling <jstirling91@gmail.com>
	 *  @author   Dave MacFarlane <david.macfarlane2@mcgill.ca>
	 *  @license  http://www.gnu.org/licenses/gpl-3.0.txt GPLv3
	 *  @link     https://github.com/mohadesz/Loris-Trunk
	 */
var SavedQueriesList=React.createClass({displayName:"SavedQueriesList",getDefaultProps:function(){},componentDidMount:function(){},loadQuery:function(queryName){this.props.onSelectQuery(this.props.queryDetails[queryName].Fields,this.props.queryDetails[queryName].Conditions)},render:function(){var queryName,curQuery,userSaved=[],globalSaved=[];if(this.props.queriesLoaded===!1)return React.createElement("div",null);for(var i=0;i<this.props.userQueries.length;i+=1)curQuery=this.props.queryDetails[this.props.userQueries[i]],console.log(curQuery.Meta),queryName=curQuery.Meta&&curQuery.Meta.name?curQuery.Meta.name:this.props.userQueries[i],userSaved.push(React.createElement("li",{key:this.props.userQueries[i]},React.createElement("a",{href:"#",onClick:this.loadQuery.bind(this,this.props.userQueries[i])},queryName)));for(var i=0;i<this.props.globalQueries.length;i+=1)curQuery=this.props.queryDetails[this.props.globalQueries[i]],console.log(curQuery.Meta),queryName=curQuery.Meta&&curQuery.Meta.name?curQuery.Meta.name:this.props.globalQueries[i],globalSaved.push(React.createElement("li",{key:this.props.globalQueries[i]},React.createElement("a",{href:"#",onClick:this.loadQuery.bind(this,this.props.globalQueries[i])},queryName)));return React.createElement("ul",{className:"nav nav-tabs navbar-right"},React.createElement("li",{className:"dropdown"},React.createElement("a",{href:"#",className:"dropdown-toggle","data-toggle":"dropdown",role:"button","aria-expanded":"false"},"Load Saved Query ",React.createElement("span",{className:"caret"})),React.createElement("ul",{className:"dropdown-menu",role:"menu"},React.createElement("li",{role:"presentation",className:"dropdown-header"},"User Saved Queries"),userSaved,React.createElement("li",{role:"presentation",className:"dropdown-header"},"Shared Saved Queries"),globalSaved)),React.createElement("li",{role:"presentation"},React.createElement("a",{href:"#SavedQueriesTab","data-toggle":"tab"},"Manage Saved Queries")))}}),DataQueryApp=React.createClass({displayName:"DataQueryApp",componentDidMount:function(){var domNode=this;$(domNode).find('a[data-toggle="tab"]').on("shown.bs.tab",function(e){$(domNode).find("li").removeClass("active"),e.target&&(e.target.classList.add("active"),e.target.parentNode&&e.target.parentNode.classList.add("active"))});var promises=[],that=this;for(var key in this.state.queryIDs){console.log(this.state.queryIDs[key][0]);for(var i=0;i<this.state.queryIDs[key].length;i+=1){var curRequest;curRequest=Promise.resolve($.ajax(loris.BaseURL+"/AjaxHelper.php?Module=dataquery&script=GetDoc.php&DocID="+that.state.queryIDs[key][i]),{data:{DocID:that.state.queryIDs[key][i]},dataType:"json"}).then(function(value){var queries=that.state.savedQueries;queries[value._id]=value,that.setState({savedQueries:queries})}),promises.push(curRequest)}}var component=(Promise.all(promises).then(function(value){that.setState({queriesLoaded:!0})}),this);$('a[data-toggle="tab"]').on("shown.bs.tab",function(e){component.setState({ActiveTab:e.target.getAttribute("href").substr(1)})})},saveFilterRule:function(rule){var savedRule={field:rule.field,operator:rule.operator,value:rule.value,instrument:rule.instrument,visit:rule.visit};return savedRule},saveFilterGroup:function(group){for(var savedFilter={activeOperator:group.activeOperator,children:[]},i=0;i<group.children.length;i++)"rule"===group.children[i].type?savedFilter.children.push(this.saveFilterRule(group.children[i])):"group"===group.children[i].type&&savedFilter.children.push(this.saveFilterGroup(group.children[i]));return savedFilter},saveCurrentQuery:function(name,shared,override){var that=this,filter=this.saveFilterGroup(this.state.filter);$.post(loris.BaseURL+"/AjaxHelper.php?Module=dataquery&script=saveQuery.php",{Fields:this.state.selectedFields,Filters:filter,QueryName:name,SharedQuery:shared,OverwriteQuery:override},function(data){var id=JSON.parse(data).id,queryIDs=that.state.queryIDs;override||(shared===!0?queryIDs.Shared.push(id):queryIDs.User.push(id)),$.get(loris.BaseURL+"/AjaxHelper.php?Module=dataquery&script=GetDoc.php&DocID="+id,function(value){var queries=that.state.savedQueries;queries[value._id]=value,that.setState({savedQueries:queries,queryIDs:queryIDs,alertLoaded:!1,alertSaved:!0,alertConflict:{show:!1}})})}).fail(function(data){409===data.status&&that.setState({alertConflict:{show:!0,QueryName:name,SharedQuery:shared}})})},overrideQuery:function(){this.saveCurrentQuery(this.state.alertConflict.QueryName,this.state.alertConflict.SharedQuery,!0)},getInitialState:function(){return{displayType:"Cross-sectional",fields:[],criteria:{},sessiondata:{},grouplevel:0,queryIDs:this.props.SavedQueries,savedQueries:{},queriesLoaded:!1,alertLoaded:!1,alertSaved:!1,alertConflict:{show:!1},ActiveTab:"Info",rowData:{},filter:{type:"group",activeOperator:0,children:[{type:"rule"}],session:this.props.AllSessions},selectedFields:{},downloadableFields:{},loading:!1}},loadFilterRule:function(rule){var script;rule.type||(rule.type="rule"),$.ajax({url:loris.BaseURL+"/AjaxHelper.php?Module=dataquery&script=datadictionary.php",success:function(data){rule.fields=data},async:!1,data:{category:rule.instrument},dataType:"json"});for(var i=0;i<rule.fields.length;i++)if(rule.fields[i].key[1]===rule.field){rule.fieldType=rule.fields[i].value.Type;break}switch(rule.operator){case"equal":script="queryEqual.php";break;case"notEqual":script="queryNotEqual.php";break;case"lessThanEqual":script="queryLessThanEqual.php";break;case"greaterThanEqual":script="queryGreaterThanEqual.php";break;case"startsWith":script="queryStartsWith.php";break;case"contains":script="queryContains.php"}return $.ajax({url:loris.BaseURL+"/AjaxHelper.php?Module=dataquery&script="+script,success:function(data){var i,allSessions={},allCandiates={};for(i=0;i<data.length;i++)allSessions[data[i][1]]||(allSessions[data[i][1]]=[]),allSessions[data[i][1]].push(data[i][0]),allCandiates[data[i][0]]||(allCandiates[data[i][0]]=[]),allCandiates[data[i][0]].push(data[i][1]);rule.candidates={allCandiates:allCandiates,allSessions:allSessions},"All"==rule.visit?rule.session=Object.keys(allCandiates):allSessions[rule.visit]?rule.session=allSessions[rule.visit]:rule.session=[]},async:!1,data:{category:rule.instrument,field:rule.field,value:rule.value},dataType:"json"}),rule},loadFilterGroup:function(group){for(var i=0;i<group.children.length;i++)group.children[i].activeOperator?(group.children[i].type||(group.children[i].type="group"),group.children[i]=this.loadFilterGroup(group.children[i])):group.children[i]=this.loadFilterRule(group.children[i]);return group.session=getSessions(group),group},loadSavedQuery:function(fields,criteria){var filterState={},selectedFields={},fieldsList=[];if(this.setState({loading:!0}),Array.isArray(criteria)){filterState={type:"group",activeOperator:0,children:[]},filterState.children=criteria.map(function(item){var fieldInfo=item.Field.split(",");switch(rule={instrument:fieldInfo[0],field:fieldInfo[1],value:item.Value,type:"rule",visit:"All"},item.Operator){case"=":rule.operator="equal";break;case"!=":rule.operator="notEqual";break;case"<=":rule.operator="lessThanEqual";break;case">=":rule.operator="greaterThanEqual";break;default:rule.operator=item.Operator}return rule});var fieldSplit;fieldsList=fields;for(var i=0;i<fields.length;i++)if(fieldSplit=fields[i].split(","),selectedFields[fieldSplit[0]]){selectedFields[fieldSplit[0]][fieldSplit[1]]={};for(var key in this.props.Visits)selectedFields[fieldSplit[0]].allVisits[key]++,selectedFields[fieldSplit[0]][fieldSplit[1]][key]=[key]}else{selectedFields[fieldSplit[0]]={},selectedFields[fieldSplit[0]][fieldSplit[1]]={},selectedFields[fieldSplit[0]].allVisits={};for(var key in this.props.Visits)selectedFields[fieldSplit[0]].allVisits[key]=1,selectedFields[fieldSplit[0]][fieldSplit[1]][key]=[key]}}else{filterState=criteria,selectedFields=fields;for(var instrument in fields)for(var field in fields[instrument])"allVisits"!==field&&fieldsList.push(instrument+","+field)}filterState.children&&filterState.children.length>0?filterState=this.loadFilterGroup(filterState):(filterState.children=[{type:"rule"}],filterState.session=this.props.AllSessions),this.setState(function(state){return{fields:fieldsList,selectedFields:selectedFields,filter:filterState,alertLoaded:!0,alertSaved:!1,loading:!1}})},fieldVisitSelect:function(action,visit,field){this.setState(function(state){var temp=state.selectedFields[field.instrument];return"check"===action?(temp[field.field][visit]=visit,temp.allVisits[visit]?temp.allVisits[visit]++:temp.allVisits[visit]=1):(delete temp[field.field][visit],1===temp.allVisits[visit]?delete temp.allVisits[visit]:temp.allVisits[visit]--),temp})},fieldChange:function(fieldName,category,downloadable){var that=this;this.setState(function(state){var selectedFields=state.selectedFields,fields=state.fields.slice(0);if(selectedFields[category])if(selectedFields[category][fieldName]){for(var key in selectedFields[category][fieldName])1===selectedFields[category].allVisits[key]?delete selectedFields[category].allVisits[key]:selectedFields[category].allVisits[key]--;delete selectedFields[category][fieldName];var idx=fields.indexOf(category+","+fieldName);fields.splice(idx,1),1===Object.keys(selectedFields[category]).length&&delete selectedFields[category],downloadable&&delete state.downloadableFields[category+","+fieldName]}else{selectedFields[category][fieldName]||(selectedFields[category][fieldName]={});for(var key in selectedFields[category].allVisits)"allVisits"!=key&&(selectedFields[category].allVisits[key]++,selectedFields[category][fieldName][key]=key);fields.push(category+","+fieldName),downloadable&&(state.downloadableFields[category+","+fieldName]=!0)}else{selectedFields[category]={},selectedFields[category][fieldName]=JSON.parse(JSON.stringify(that.props.Visits)),selectedFields[category].allVisits={};for(var key in that.props.Visits)selectedFields[category].allVisits[key]=1;fields.push(category+","+fieldName),downloadable&&(state.downloadableFields[category+","+fieldName]=!0)}return{selectedFields:selectedFields,fields:fields}})},getSessions:function(){return this.state.filter.children.length>0?this.state.filter.session:this.props.AllSessions},runQuery:function(fields,sessions){var sectionedSessions,DocTypes=[],that=this,semaphore=0,ajaxComplete=function(){if(0==semaphore){var rowdata=that.getRowData(that.state.grouplevel);that.setState({rowData:rowdata,loading:!1})}};this.setState({rowData:{},sessiondata:{},loading:!0});for(var i=0;i<fields.length;i+=1){var field_split=fields[i].split(","),category=field_split[0];if(DocTypes.indexOf(category)===-1){for(var sessionInfo=[],j=0;j<this.state.filter.session.length;j++)if(Array.isArray(this.state.filter.session[j]))this.state.selectedFields[category].allVisits[this.state.filter.session[j][1]]&&sessionInfo.push(this.state.filter.session[j]);else for(var key in this.state.selectedFields[category].allVisits){var temp=[];temp.push(this.state.filter.session[j]),temp.push(key),sessionInfo.push(temp)}DocTypes.push(category),semaphore++,sectionedSessions=JSON.stringify(sessionInfo),$.ajax({type:"POST",url:loris.BaseURL+"/AjaxHelper.php?Module=dataquery&script=retrieveCategoryDocs.php",data:{DocType:category,Sessions:sectionedSessions},dataType:"text",success:function(data){if(data){var i,row,rows,identifier,sessiondata=that.state.sessiondata;for(data=JSON.parse(data),rows=data.rows,i=0;i<rows.length;i+=1)row=rows[i],identifier=row.value,sessiondata.hasOwnProperty(identifier)||(sessiondata[identifier]={}),sessiondata[identifier][row.key[0]]=row.doc;that.setState({sessiondata:sessiondata})}console.log("Received data"),semaphore--,ajaxComplete()}})}}},getRowData:function(displayID){var i,href,sessiondata=this.state.sessiondata,fields=(this.getSessions(),this.state.fields.sort()),downloadableFields=this.state.downloadableFields,rowdata=[],currow=[],Identifiers=[],RowHeaders=[],fileData=[];if(0===displayID){for(i=0;fields&&i<fields.length;i+=1)RowHeaders.push(fields[i]);for(var session in sessiondata){for(currow=[],i=0;fields&&i<fields.length;i+=1){var fieldSplit=fields[i].split(",");currow[i]=".";var sd=sessiondata[session];sd[fieldSplit[0]]&&sd[fieldSplit[0]].data[fieldSplit[1]]&&downloadableFields[fields[i]]?(href=loris.BaseURL+"/mri/jiv/get_file.php?file="+sd[fieldSplit[0]].data[fieldSplit[1]],currow[i]=React.createElement("a",{href:href},sd[fieldSplit[0]].data[fieldSplit[1]]),fileData.push("file/"+sd[fieldSplit[0]]._id+"/"+encodeURIComponent(sd[fieldSplit[0]].data[fieldSplit[1]]))):sd[fieldSplit[0]]&&(currow[i]=sd[fieldSplit[0]].data[fieldSplit[1]])}rowdata.push(currow),Identifiers.push(session)}}else{var visit,identifier,temp,colHeader,index,instrument,fieldSplit,Visits={};for(var session in sessiondata)temp=session.split(","),visit=temp[1],Visits[visit]||(Visits[visit]=!0),identifier=temp[0],Identifiers.indexOf(identifier)===-1&&Identifiers.push(identifier);for(i=0;fields&&i<fields.length;i+=1)for(visit in Visits)temp=fields[i].split(","),instrument=this.state.selectedFields[temp[0]],instrument&&instrument[temp[1]]&&instrument[temp[1]][visit]&&RowHeaders.push(visit+" "+fields[i]);for(identifier in Identifiers){currow=[];for(colHeader in RowHeaders)temp=Identifiers[identifier]+","+RowHeaders[colHeader].split(" ")[0],index=sessiondata[temp],index?(temp=index[RowHeaders[colHeader].split(",")[0].split(" ")[1]],fieldSplit=RowHeaders[colHeader].split(" ")[1].split(","),temp?temp.data[RowHeaders[colHeader].split(",")[1]]&&downloadableFields[fieldSplit[0]+","+fieldSplit[1]]?(href=loris.BaseURL+"/mri/jiv/get_file.php?file="+temp.data[RowHeaders[colHeader].split(",")[1]],temp=React.createElement("a",{href:href},temp.data[RowHeaders[colHeader].split(",")[1]])):temp=temp.data[RowHeaders[colHeader].split(",")[1]]:temp=".",currow.push(temp)):currow.push(".");rowdata.push(currow)}}return{rowdata:rowdata,Identifiers:Identifiers,RowHeaders:RowHeaders,fileData:fileData}},dismissAlert:function(){this.setState({alertLoaded:!1,alertSaved:!1,alertConflict:{show:!1}})},resetQuery:function(){this.setState({fields:[],criteria:{},selectedFields:{}})},changeDataDisplay:function(displayID){var rowdata=this.getRowData(displayID);this.setState({grouplevel:displayID,rowData:rowdata})},updateFilter:function(filter){var that=this;this.setState(function(state){return 0===filter.children.length&&(filter.session=that.props.AllSessions),{filter:filter}})},render:function(){var tabs=[],alert=React.createElement("div",null);tabs.push(React.createElement(InfoTabPane,{TabId:"Info",UpdatedTime:this.props.UpdatedTime,Loading:this.state.loading})),tabs.push(React.createElement(FieldSelectTabPane,{TabId:"DefineFields",categories:this.props.categories,onFieldChange:this.fieldChange,selectedFields:this.state.selectedFields,Visits:this.props.Visits,fieldVisitSelect:this.fieldVisitSelect,Loading:this.state.loading})),tabs.push(React.createElement(FilterSelectTabPane,{TabId:"DefineFilters",categories:this.props.categories,filter:this.state.filter,updateFilter:this.updateFilter,Visits:this.props.Visits,Loading:this.state.loading}));var displayType=0===this.state.grouplevel?"Cross-sectional":"Longitudinal";tabs.push(React.createElement(ViewDataTabPane,{TabId:"ViewData",Fields:this.state.fields,Criteria:this.state.criteria,Sessions:this.getSessions(),Data:this.state.rowData.rowdata,RowInfo:this.state.rowData.Identifiers,RowHeaders:this.state.rowData.RowHeaders,FileData:this.state.rowData.fileData,onRunQueryClicked:this.runQuery,displayType:displayType,changeDataDisplay:this.changeDataDisplay,Loading:this.state.loading})),tabs.push(React.createElement(StatsVisualizationTabPane,{TabId:"Statistics",Fields:this.state.rowData.RowHeaders,Data:this.state.rowData.rowdata,Loading:this.state.loading})),tabs.push(React.createElement(ManageSavedQueriesTabPane,{TabId:"SavedQueriesTab",userQueries:this.state.queryIDs.User,globalQueries:this.state.queryIDs.Shared,onSaveQuery:this.saveCurrentQuery,queryDetails:this.state.savedQueries,queriesLoaded:this.state.queriesLoaded,Loading:this.state.loading})),this.state.alertLoaded&&(alert=React.createElement("div",{className:"alert alert-success",role:"alert"},React.createElement("button",{type:"button",className:"close","aria-label":"Close",onClick:this.dismissAlert},React.createElement("span",{"aria-hidden":"true"},"×")),React.createElement("strong",null,"Success")," Query Loaded.")),this.state.alertSaved&&(alert=React.createElement("div",{className:"alert alert-success",role:"alert"},React.createElement("button",{type:"button",className:"close","aria-label":"Close",onClick:this.dismissAlert},React.createElement("span",{"aria-hidden":"true"},"×")),React.createElement("strong",null,"Success")," Query Saved.")),this.state.alertConflict.show&&(alert=React.createElement("div",{className:"alert alert-warning",role:"alert"},React.createElement("button",{type:"button",className:"close","aria-label":"Close",onClick:this.dismissAlert},React.createElement("span",{"aria-hidden":"true"},"×")),React.createElement("button",{type:"button",className:"close","aria-label":"Close",onClick:this.dismissAlert},React.createElement("span",{"aria-hidden":"true"},"Override")),React.createElement("strong",null,"Error")," Query with the same name already exists.",React.createElement("a",{href:"#",class:"alert-link",onClick:this.overrideQuery},"Click here to override")));var widthClass="col-md-12",sideBar=React.createElement("div",null);return this.state.fields.length>0&&"ViewData"!==this.state.ActiveTab&&"Statistics"!==this.state.ActiveTab&&"Info"!==this.state.ActiveTab&&(widthClass="col-md-10",sideBar=React.createElement("div",{className:"col-md-2"},React.createElement(FieldsSidebar,{Fields:this.state.fields,Criteria:this.state.criteria,resetQuery:this.resetQuery}))),React.createElement("div",null,alert,React.createElement("div",{className:widthClass},React.createElement("nav",{className:"nav nav-tabs"},React.createElement("ul",{className:"nav nav-tabs navbar-left","data-tabs":"tabs"},React.createElement("li",{role:"presentation",className:"active"},React.createElement("a",{href:"#Info","data-toggle":"tab"},"Info")),React.createElement("li",{role:"presentation"},React.createElement("a",{href:"#DefineFields","data-toggle":"tab"},"Define Fields")),React.createElement("li",{role:"presentation"},React.createElement("a",{href:"#DefineFilters","data-toggle":"tab"},"Define Filters")),React.createElement("li",{role:"presentation"},React.createElement("a",{href:"#ViewData","data-toggle":"tab"},"View Data")),React.createElement("li",{role:"presentation"},React.createElement("a",{href:"#Statistics","data-toggle":"tab"},"Statistical Analysis"))),React.createElement(SavedQueriesList,{userQueries:this.state.queryIDs.User,globalQueries:this.state.queryIDs.Shared,queryDetails:this.state.savedQueries,queriesLoaded:this.state.queriesLoaded,onSelectQuery:this.loadSavedQuery,loadedQuery:this.state.loadedQuery})),React.createElement("div",{className:"tab-content"},tabs)),sideBar)}});window.SavedQueriesList=SavedQueriesList,window.DataQueryApp=DataQueryApp,window.RDataQueryApp=React.createFactory(DataQueryApp),exports.default=DataQueryApp}]);
//# sourceMappingURL=react.app.js.map