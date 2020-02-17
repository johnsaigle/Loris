SET FOREIGN_KEY_CHECKS=0;
TRUNCATE TABLE `permissions`;
LOCK TABLES `permissions` WRITE;
INSERT INTO `permissions` (`permID`, `code`, `description`, `categoryID`) VALUES (1,'superuser','Superuser - supersedes all permissions',1);
INSERT INTO `permissions` (`permID`, `code`, `description`, `categoryID`) VALUES (2,'user_accounts','User management',2);
INSERT INTO `permissions` (`permID`, `code`, `description`, `categoryID`) VALUES (3,'user_accounts_multisite','Across all sites create and edit users',2);
INSERT INTO `permissions` (`permID`, `code`, `description`, `categoryID`) VALUES (4,'context_help','Edit help documentation',2);
INSERT INTO `permissions` (`permID`, `code`, `description`, `categoryID`) VALUES (5,'bvl_feedback','Behavioural QC',1);
INSERT INTO `permissions` (`permID`, `code`, `description`, `categoryID`) VALUES (6,'imaging_browser_qc','Edit imaging browser QC status',2);
INSERT INTO `permissions` (`permID`, `code`, `description`, `categoryID`) VALUES (7,'mri_efax','Edit MRI Efax files',2);
INSERT INTO `permissions` (`permID`, `code`, `description`, `categoryID`) VALUES (8,'send_to_dcc','Send to DCC',2);
INSERT INTO `permissions` (`permID`, `code`, `description`, `categoryID`) VALUES (9,'unsend_to_dcc','Reverse Send from DCC',2);
INSERT INTO `permissions` (`permID`, `code`, `description`, `categoryID`) VALUES (10,'access_all_profiles','Across all sites access candidate profiles',2);
INSERT INTO `permissions` (`permID`, `code`, `description`, `categoryID`) VALUES (11,'data_entry','Data entry',1);
INSERT INTO `permissions` (`permID`, `code`, `description`, `categoryID`) VALUES (12,'examiner_view','Add and certify examiners',2);
INSERT INTO `permissions` (`permID`, `code`, `description`, `categoryID`) VALUES (13,'examiner_multisite','Across all sites add and certify examiners',2);
INSERT INTO `permissions` (`permID`, `code`, `description`, `categoryID`) VALUES (14,'training','View and complete training',2);
INSERT INTO `permissions` (`permID`, `code`, `description`, `categoryID`) VALUES (15,'timepoint_flag','Edit exclusion flags',2);
INSERT INTO `permissions` (`permID`, `code`, `description`, `categoryID`) VALUES (16,'timepoint_flag_evaluate','Evaluate overall exclusionary criteria for the timepoint',2);
INSERT INTO `permissions` (`permID`, `code`, `description`, `categoryID`) VALUES (17,'conflict_resolver','Resolving conflicts',2);
INSERT INTO `permissions` (`permID`, `code`, `description`, `categoryID`) VALUES (18,'data_dict_view','View Data Dictionary (Parameter type descriptions)',2);
INSERT INTO `permissions` (`permID`, `code`, `description`, `categoryID`) VALUES (19,'violated_scans_view_allsites','Violated Scans: View all-sites Violated Scans',2);
INSERT INTO `permissions` (`permID`, `code`, `description`, `categoryID`) VALUES (20,'violated_scans_edit','Violated Scans: Edit MRI protocol table',2);
INSERT INTO `permissions` (`permID`, `code`, `description`, `categoryID`) VALUES (22,'config','Edit configuration settings',2);
INSERT INTO `permissions` (`permID`, `code`, `description`, `categoryID`) VALUES (23,'imaging_browser_view_site','View own-site Imaging Browser pages',2);
INSERT INTO `permissions` (`permID`, `code`, `description`, `categoryID`) VALUES (24,'imaging_browser_view_allsites','View all-sites Imaging Browser pages',2);
INSERT INTO `permissions` (`permID`, `code`, `description`, `categoryID`) VALUES (25,'dicom_archive_view_allsites','Across all sites view Dicom Archive module and pages',2);
INSERT INTO `permissions` (`permID`, `code`, `description`, `categoryID`) VALUES (26,'reliability_edit_all','Access and Edit all Reliability profiles',2);
INSERT INTO `permissions` (`permID`, `code`, `description`, `categoryID`) VALUES (27,'reliability_swap_candidates','Swap Reliability candidates across all sites',2);
INSERT INTO `permissions` (`permID`, `code`, `description`, `categoryID`) VALUES (28,'instrument_builder','Instrument Builder: Create and Edit instrument forms',2);
INSERT INTO `permissions` (`permID`, `code`, `description`, `categoryID`) VALUES (29,'data_dict_edit','Edit Data Dictionary',2);
INSERT INTO `permissions` (`permID`, `code`, `description`, `categoryID`) VALUES (30,'quality_control','Quality Control access',2);
INSERT INTO `permissions` (`permID`, `code`, `description`, `categoryID`) VALUES (31,'candidate_parameter_view','View Candidate Parameters',2);
INSERT INTO `permissions` (`permID`, `code`, `description`, `categoryID`) VALUES (32,'candidate_parameter_edit','Edit Candidate Parameters',2);
INSERT INTO `permissions` (`permID`, `code`, `description`, `categoryID`) VALUES (33,'genomic_browser_view_site','View Genomic Browser data from own site',2);
INSERT INTO `permissions` (`permID`, `code`, `description`, `categoryID`) VALUES (34,'genomic_browser_view_allsites','View Genomic Browser data across all sites',2);
INSERT INTO `permissions` (`permID`, `code`, `description`, `categoryID`) VALUES (35,'document_repository_view','View and upload files in Document Repository',2);
INSERT INTO `permissions` (`permID`, `code`, `description`, `categoryID`) VALUES (36,'document_repository_delete','Delete files in Document Repository',2);
INSERT INTO `permissions` (`permID`, `code`, `description`, `categoryID`) VALUES (37,'server_processes_manager','View and manage server processes',2);
INSERT INTO `permissions` (`permID`, `code`, `description`, `categoryID`) VALUES (38,'imaging_uploader','Imaging Uploader',2);
INSERT INTO `permissions` (`permID`, `code`, `description`, `categoryID`) VALUES (39,'acknowledgements_view','View Acknowledgements',2);
INSERT INTO `permissions` (`permID`, `code`, `description`, `categoryID`) VALUES (40,'acknowledgements_edit','Edit Acknowledgements',2);
INSERT INTO `permissions` (`permID`, `code`, `description`, `categoryID`) VALUES (41,'dataquery_view','View Data Query Tool',2);
INSERT INTO `permissions` (`permID`, `code`, `description`, `categoryID`) VALUES (42,'genomic_data_manager','Manage the genomic files',2);
INSERT INTO `permissions` (`permID`, `code`, `description`, `categoryID`) VALUES (43,'media_write','Media files: Uploading/Downloading/Editing',2);
INSERT INTO `permissions` (`permID`, `code`, `description`, `categoryID`) VALUES (44,'media_read','Media files: Browsing',2);
INSERT INTO `permissions` (`permID`, `code`, `description`, `categoryID`) VALUES (45,'issue_tracker_reporter','Can add a new issue, edit own issue, comment on all',2);
INSERT INTO `permissions` (`permID`, `code`, `description`, `categoryID`) VALUES (46,'issue_tracker_developer','Can re-assign issues, mark issues as closed, comment on all, edit issues.',2);
INSERT INTO `permissions` (`permID`, `code`, `description`, `categoryID`) VALUES (47,'imaging_browser_phantom_allsites','Can access only phantom data from all sites in Imaging Browser',2);
INSERT INTO `permissions` (`permID`, `code`, `description`, `categoryID`) VALUES (48,'imaging_browser_phantom_ownsite','Can access only phantom data from own site in Imaging Browser',2);
INSERT INTO `permissions` (`permID`, `code`, `description`, `categoryID`) VALUES (49,'instrument_manager_read','Instrument Manager: View module',2);
INSERT INTO `permissions` (`permID`, `code`, `description`, `categoryID`) VALUES (50,'instrument_manager_write','Instrument Manager: Install new instruments via file upload',2);
INSERT INTO `permissions` (`permID`, `code`, `description`, `categoryID`) VALUES (51,'data_release_upload','Data Release: Upload file',2);
INSERT INTO `permissions` (`permID`, `code`, `description`, `categoryID`) VALUES (52,'data_release_edit_file_access','Data Release: Grant other users view-file permissions',2);
INSERT INTO `permissions` (`permID`, `code`, `description`, `categoryID`) VALUES (53,'publication_view','Publication - Access to module',2);
INSERT INTO `permissions` (`permID`, `code`, `description`, `categoryID`) VALUES (54,'publication_propose','Publication - Propose a project',2);
INSERT INTO `permissions` (`permID`, `code`, `description`, `categoryID`) VALUES (55,'publication_approve','Publication - Approve or reject proposed publication projects',2);
INSERT INTO `permissions` (`permID`, `code`, `description`, `categoryID`) VALUES (56,'data_release_view','Data Release: View releases',2);
INSERT INTO `permissions` (`permID`, `code`, `description`, `categoryID`) VALUES (57,'candidate_dob_edit','Edit dates of birth',2);
INSERT INTO `permissions` (`permID`, `code`, `description`, `categoryID`) VALUES (58,'battery_manager_view','View Battery Manager',2);
INSERT INTO `permissions` (`permID`, `code`, `description`, `categoryID`) VALUES (59,'battery_manager_edit','Add, activate, and deactivate entries in Test Battery',2);
INSERT INTO `permissions` (`permID`, `code`, `description`, `categoryID`) VALUES (60,'electrophysiology_browser_view_allsites','View all-sites Electrophysiology Browser pages',2);
INSERT INTO `permissions` (`permID`, `code`, `description`, `categoryID`) VALUES (61,'electrophysiology_browser_view_site','View own site Electrophysiology Browser pages',2);
INSERT INTO `permissions` (`permID`, `code`, `description`, `categoryID`) VALUES (62,'module_manager_view','Module Manager: access the module',2);
INSERT INTO `permissions` (`permID`, `code`, `description`, `categoryID`) VALUES (63,'module_manager_edit','Module Manager: edit installed modules',2);
INSERT INTO `permissions` (`permID`, `code`, `description`, `categoryID`) VALUES (64,'candidate_dod_edit','Edit dates of death',2);
INSERT INTO `permissions` (`permID`, `code`, `description`, `categoryID`) VALUES (65,'violated_scans_view_ownsite','Violated Scans: View Violated Scans from own site',2);
INSERT INTO `permissions` (`permID`, `code`, `description`, `categoryID`) VALUES (66,'document_repository_edit','Document Repository: Upload and edit files',2);
INSERT INTO `permissions` (`permID`, `code`, `description`, `categoryID`) VALUES (67,'survey_accounts_view','Survey Accounts: view module',2);
INSERT INTO `permissions` (`permID`, `code`, `description`, `categoryID`) VALUES (68,'imaging_quality_control_view','Imaging Quality Control: view module',2);
INSERT INTO `permissions` (`permID`, `code`, `description`, `categoryID`) VALUES (69,'behavioural_quality_control_view','Behavioural Quality Control: view module',2);
INSERT INTO `permissions` (`permID`, `code`, `description`, `categoryID`) VALUES (64,'survey_accounts_view','Survey Accounts: view module',2);
INSERT INTO `permissions` (`permID`, `code`, `description`, `categoryID`) VALUES (65,'imaging_quality_control_view','Imaging Quality Control: view module',2);
INSERT INTO `permissions` (`permID`, `code`, `description`, `categoryID`) VALUES (66,'behavioural_quality_control_view','Behavioural Quality Control: view module',2);
UNLOCK TABLES;
SET FOREIGN_KEY_CHECKS=1;
