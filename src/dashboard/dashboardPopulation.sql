TRUNCATE TABLE `Dashboard_Asset`;
TRUNCATE TABLE `Dashboard_UserGroup`;
TRUNCATE TABLE `Dashboard_Group`;
TRUNCATE TABLE `Dashboard_Entity`;
TRUNCATE TABLE `Dashboard_Profile`;
TRUNCATE TABLE `Dashboard_UserProfile`;
TRUNCATE TABLE `Dashboard_User`;
TRUNCATE TABLE `Dashboard_Locations`;
TRUNCATE TABLE `Dashboard_Model`;
TRUNCATE TABLE `Dashboard_AssetType`;
TRUNCATE TABLE `Dashboard_Type`;


--insert data
insert into `Dashboard_Entity` (id, name, parentId) select id, name, case when entities_id < 0 then 0 else entities_id end as entities_id from glpi_entities;


--insert data
insert into `Dashboard_Group` (id, name,entityId) select id,name,entities_id from glpi_groups;

--insert data
insert into `Dashboard_User` (id, name, groupId, profileId) select id, name,0,0 from glpi_users where is_deleted = 0 and is_active=1;

--insert data
insert into `Dashboard_Locations` (id, name) select id,name from glpi_locations;


--insert data
INSERT INTO `Dashboard_AssetType` (id, name) VALUES (1,'computers'),(5,'devices'),(9,'enclosures'),(2,'monitors'),(4,'network_devices'),(10,'pdu'),(7,'phones'),(6,'printers'),(8,'racks'),(11,'simcards'),(3,'softwares');


--insert data
insert into `Dashboard_Type` (id,name,assetTypeId) select id,name,1 from glpi_computertypes;
insert into `Dashboard_Type` (id,name,assetTypeId) select id,name,2 from glpi_monitortypes;

--insert data
insert into `Dashboard_Model` (id,name,assetTypeId) select id,name,1 from glpi_computermodels;
insert into `Dashboard_Model` (id,name,assetTypeId) select id,name,2 from glpi_monitormodels;

--insert data
insert into Dashboard_Asset (name,entityId,assetTypeId,locationId,modelId,typeId) select name,entities_id,1,locations_id,computermodels_id,computertypes_id from glpi_computers;
insert into Dashboard_Asset (name,entityId,assetTypeId,locationId,modelId,typeId) select name,entities_id,2,locations_id,monitormodels_id,monitortypes_id from glpi_monitors;