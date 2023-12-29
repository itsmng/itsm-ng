SET foreign_key_checks = 0;

TRUNCATE TABLE `Dashboard_Entity`;
TRUNCATE TABLE `Dashboard_Profile`;
TRUNCATE TABLE `Dashboard_Group`;
TRUNCATE TABLE `Dashboard_User`;
TRUNCATE TABLE `Dashboard_Location`;
TRUNCATE TABLE `Dashboard_AssetType`;
TRUNCATE TABLE `Dashboard_Type`;
TRUNCATE TABLE `Dashboard_Model`;
TRUNCATE TABLE `Dashboard_Asset`;
TRUNCATE TABLE `_Dashboard_ProfileEntityToDashboard_User`;
TRUNCATE TABLE `_Dashboard_GroupToDashboard_User`;


INSERT INTO `Dashboard_Entity`(id, NAME, parentId)
SELECT id, NAME,
    CASE
        WHEN entities_id < 0 THEN NULL
        ELSE entities_id
    END
AS entities_id
FROM glpi_entities;


INSERT INTO `Dashboard_Group` (id, name, entityId)
SELECT id, name, entities_id
FROM glpi_groups;

INSERT INTO `Dashboard_User` (id, name)
SELECT id, name
FROM glpi_users
WHERE is_deleted = 0 AND is_active=1;

INSERT INTO `Dashboard_Location` (id, name)
SELECT id,name
FROM glpi_locations;

INSERT INTO `Dashboard_AssetType` (id, name) VALUES
    (1, 'computers'),
    (5, 'devices'),
    (9, 'enclosures'),
    (2, 'monitors'),
    (4, 'network_devices'),
    (10, 'pdu'),
    (7, 'phones'),
    (6, 'printers'),
    (8, 'racks'),
    (11, 'simcards'),
    (3, 'softwares');

INSERT INTO `Dashboard_Type` (id, name, assetTypeId)
SELECT id, name, 1
FROM glpi_computertypes;

INSERT INTO `Dashboard_Type` (id, name, assetTypeId)
SELECT id,name,2
FROM glpi_monitortypes;

INSERT INTO `Dashboard_Model` (id, name, assetTypeId)
SELECT id, name, 1 
FROM glpi_computermodels;

INSERT INTO `Dashboard_Model` (id, name, assetTypeId)
SELECT id, name, 2
FROM glpi_monitormodels;

INSERT INTO Dashboard_Asset (id, name, entityId, assetTypeId, locationId, modelId, typeId)
SELECT id, name, entities_id, 1, locations_id, computermodels_id, computertypes_id
FROM glpi_computers;

INSERT INTO Dashboard_Asset (id, name, entityId, assetTypeId, locationId, modelId, typeId)
SELECT id, name, entities_id, 2, locations_id, monitormodels_id, monitortypes_id
FROM glpi_monitors;

SET foreign_key_checks = 1;