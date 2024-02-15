SET foreign_key_checks = 0;

TRUNCATE TABLE `Dashboard_Profile`;
TRUNCATE TABLE `_Dashboard_ProfileEntityToDashboard_User`;
TRUNCATE TABLE `_Dashboard_GroupToDashboard_User`;


TRUNCATE TABLE `Dashboard_Entity`;
INSERT INTO `Dashboard_Entity`(id, NAME, parentId)
SELECT id, NAME,
    CASE
        WHEN entities_id < 0 THEN NULL
        ELSE entities_id
    END
AS entities_id
FROM glpi_entities;


TRUNCATE TABLE `Dashboard_Group`;
INSERT INTO `Dashboard_Group` (id, name, entityId)
SELECT id, name, entities_id
FROM glpi_groups;

TRUNCATE TABLE `Dashboard_User`;
INSERT INTO `Dashboard_User` (id, name)
SELECT id, name
FROM glpi_users
WHERE is_deleted = 0 AND is_active=1;

TRUNCATE TABLE `Dashboard_Location`;
INSERT INTO `Dashboard_Location` (id, name)
SELECT id,name
FROM glpi_locations;

TRUNCATE TABLE `Dashboard_AssetType`;
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

TRUNCATE TABLE `Dashboard_Type`;
INSERT INTO `Dashboard_Type` (id, name, assetTypeId)
SELECT id, name, 1
FROM glpi_computertypes;

INSERT INTO `Dashboard_Type` (id, name, assetTypeId)
SELECT id,name,2
FROM glpi_monitortypes;

TRUNCATE TABLE `Dashboard_Model`;
INSERT INTO `Dashboard_Model` (id, name, assetTypeId)
SELECT id, name, 1 
FROM glpi_computermodels;

INSERT INTO `Dashboard_Model` (id, name, assetTypeId)
SELECT id, name, 2
FROM glpi_monitormodels;

TRUNCATE TABLE `Dashboard_Asset`;
INSERT INTO `Dashboard_Asset` (id, name, entityId, assetTypeId, locationId, modelId, typeId)
SELECT id, name, entities_id, 1, locations_id, computermodels_id, computertypes_id
FROM glpi_computers;

INSERT INTO `Dashboard_Asset` (id, name, entityId, assetTypeId, locationId, modelId, typeId)
SELECT id, name, entities_id, 2, locations_id, monitormodels_id, monitortypes_id
FROM glpi_monitors;

TRUNCATE TABLE `Dashboard_Impact`;
INSERT INTO Dashboard_Impact (id, name) VALUES
    (1, 'Very High'),
    (2, 'High'),
    (3, 'Medium'),
    (4, 'Low'),
    (5, 'Very Low');

TRUNCATE TABLE `Dashboard_Urgency`;
INSERT INTO Dashboard_Urgency (id, name) VALUES
    (1, 'Very High'),
    (2, 'High'),
    (3, 'Medium'),
    (4, 'Low'),
    (5, 'Very Low');

TRUNCATE TABLE `Dashboard_Priority`;
INSERT INTO Dashboard_Priority (id, name) VALUES
    (1, 'Very High'),
    (2, 'High'),
    (3, 'Medium'),
    (4, 'Low'),
    (5, 'Very Low');

TRUNCATE TABLE `Dashboard_ITILCategory`;
INSERT INTO `Dashboard_ITILCategory` (id, name) VALUES (0, 'null');
INSERT INTO `Dashboard_ITILCategory` (id, name)
SELECT id, name
FROM glpi_itilcategories;

TRUNCATE TABLE `Dashboard_TicketStatus`;
INSERT INTO `Dashboard_TicketStatus` (id, name) VALUES
    (1, 'New'),
    (2, 'Processing (assigned)'),
    (3, 'Processing (planned)'),
    (4, 'Pending'),
    ('5', 'Solved'),
    ('6', 'Closed');

TRUNCATE TABLE `Dashboard_TicketType`;
INSERT INTO `Dashboard_TicketType` (id, name) VALUES
    (1, 'Incident'),
    (2, 'Request');

TRUNCATE TABLE `Dashboard_Ticket`;
INSERT INTO `Dashboard_Ticket` (id, name, entityId, date, closeDate, solveDate, statusId, typeId, recipientId, urgencyId, impactId, priorityId, itilCategoryId)
SELECT id, name, entities_id, date, closedate, solvedate, status, type, users_id_recipient, urgency, impact, priority, itilcategories_id
FROM glpi_tickets;

SET foreign_key_checks = 1;