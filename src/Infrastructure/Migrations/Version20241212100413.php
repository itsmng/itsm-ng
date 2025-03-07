<?php

declare(strict_types=1);

namespace Itsmng\Infrastructure\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241212100413 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {

        $transformFkTables = [
             'glpi_appliances' => [
                 ['name' => 'A90A053DED775E23', 'origin' => 'locations_id', 'ref' => 'glpi_locations'],
                 ['name' => 'A90A053DA2A4C2E4', 'origin' => 'manufacturers_id', 'ref' => 'glpi_manufacturers'],
                 ['name' => 'A90A053D72B0F067', 'origin' => 'applianceenvironments_id', 'ref' => 'glpi_applianceenvironments'],
                 ['name' => 'A90A053D67B3B43D', 'origin' => 'users_id', 'ref' => 'glpi_users'],
                 ['name' => 'A90A053DFD9C58DA', 'origin' => 'users_id_tech', 'ref' => 'glpi_users'],
                 ['name' => 'A90A053DF373DCF', 'origin' => 'groups_id', 'ref' => 'glpi_groups'],
                 ['name' => 'A90A053D1421F0A5', 'origin' => 'groups_id_tech', 'ref' => 'glpi_groups'],
                 ['name' => 'A90A053DB17973F', 'origin' => 'states_id', 'ref' => 'glpi_states'],
             ],
             'glpi_appliances_items' => [
                 ['name' => 'A2361859C592445B', 'origin' => 'appliances_id', 'ref' => 'glpi_appliances'],
             ],
             'glpi_authldapreplicates' => [
                 ['name' => '89F2E7A47D03EC85', 'origin' => 'authldaps_id', 'ref' => 'glpi_authldaps'],
             ],
             'glpi_budgets' => [
                 ['name' => 'B6985E2CED775E23', 'origin' => 'locations_id', 'ref' => 'glpi_locations'],
                 ['name' => 'B6985E2C387998CB', 'origin' => 'budgettypes_id', 'ref' => 'glpi_budgettypes'],
             ],
             'glpi_businesscriticities' => [
                 ['name' => '5119F8B4FCE88FAB', 'origin' => 'businesscriticities_id', 'ref' => 'glpi_businesscriticities'],
             ],
             'glpi_calendars_holidays' => [
                 ['name' => '2315C8B372C4B705', 'origin' => 'calendars_id', 'ref' => 'glpi_calendars'],
                 ['name' => '2315C8B37C9675AB', 'origin' => 'holidays_id', 'ref' => 'glpi_holidays'],
             ],
             'glpi_calendarsegments' => [
                 ['name' => '8021521D72C4B705', 'origin' => 'calendars_id', 'ref' => 'glpi_calendars'],
             ],
             'glpi_cartridgeitems' => [
                 ['name' => '988DAA3DED775E23', 'origin' => 'locations_id', 'ref' => 'glpi_locations'],
                 ['name' => '988DAA3D31A4834B', 'origin' => 'cartridgeitemtypes_id', 'ref' => 'glpi_cartridgeitemtypes'],
                 ['name' => '988DAA3DA2A4C2E4', 'origin' => 'manufacturers_id', 'ref' => 'glpi_manufacturers'],
                 ['name' => '988DAA3DFD9C58DA', 'origin' => 'users_id_tech', 'ref' => 'glpi_users'],
                 ['name' => '988DAA3D1421F0A5', 'origin' => 'groups_id_tech', 'ref' => 'glpi_groups'],
             ],
             'glpi_cartridgeitems_printermodels' => [
                 ['name' => '856AD7A787A366A1', 'origin' => 'cartridgeitems_id', 'ref' => 'glpi_cartridgeitems'],
                 ['name' => '856AD7A780854B45', 'origin' => 'printermodels_id', 'ref' => 'glpi_printermodels'],
             ],
             'glpi_cartridges' => [
                 ['name' => '3185A7C787A366A1', 'origin' => 'cartridgeitems_id', 'ref' => 'glpi_cartridgeitems'],
                 ['name' => '3185A7C7713EF9E2', 'origin' => 'printers_id', 'ref' => 'glpi_printers'],
             ],
             'glpi_certificates' => [
                 ['name' => 'F825F106AF5961F5', 'origin' => 'certificatetypes_id', 'ref' => 'glpi_certificatetypes'],
                 ['name' => 'F825F106FD9C58DA', 'origin' => 'users_id_tech', 'ref' => 'glpi_users'],
                 ['name' => 'F825F1061421F0A5', 'origin' => 'groups_id_tech', 'ref' => 'glpi_groups'],
                 ['name' => 'F825F106ED775E23', 'origin' => 'locations_id', 'ref' => 'glpi_locations'],
                 ['name' => 'F825F106A2A4C2E4', 'origin' => 'manufacturers_id', 'ref' => 'glpi_manufacturers'],
                 ['name' => 'F825F10667B3B43D', 'origin' => 'users_id', 'ref' => 'glpi_users'],
                 ['name' => 'F825F106F373DCF', 'origin' => 'groups_id', 'ref' => 'glpi_groups'],
                 ['name' => 'F825F106B17973F', 'origin' => 'states_id', 'ref' => 'glpi_states'],
             ],
             'glpi_certificates_items' => [
                 ['name' => 'E410E24524E411BB', 'origin' => 'certificates_id', 'ref' => 'glpi_certificates'],
             ],
             'glpi_changecosts' => [
                 ['name' => 'F846ABCA5D80B7AB', 'origin' => 'changes_id', 'ref' => 'glpi_changes'],
                 ['name' => 'F846ABCA22FD2D3D', 'origin' => 'budgets_id', 'ref' => 'glpi_budgets'],
             ],
             'glpi_changes' => [
                 ['name' => '4A127359BB756162', 'origin' => 'users_id_recipient', 'ref' => 'glpi_users'],
                 ['name' => '4A12735927D112BD', 'origin' => 'users_id_lastupdater', 'ref' => 'glpi_users'],
                 ['name' => '4A127359EFE9C34D', 'origin' => 'itilcategories_id', 'ref' => 'glpi_itilcategories'],
             ],
             'glpi_changes_groups' => [
                 ['name' => 'DC2C71435D80B7AB', 'origin' => 'changes_id', 'ref' => 'glpi_changes'],
                 ['name' => 'DC2C7143F373DCF', 'origin' => 'groups_id', 'ref' => 'glpi_groups'],
             ],
             'glpi_changes_items' => [
                 ['name' => '79C851F35D80B7AB', 'origin' => 'changes_id', 'ref' => 'glpi_changes'],
             ],
             'glpi_changes_problems' => [
                 ['name' => '20AE2965D80B7AB', 'origin' => 'changes_id', 'ref' => 'glpi_changes'],
                 ['name' => '20AE296E30A47DD', 'origin' => 'problems_id', 'ref' => 'glpi_problems'],
             ],
             'glpi_changes_suppliers' => [
                 ['name' => 'B37E56B25D80B7AB', 'origin' => 'changes_id', 'ref' => 'glpi_changes'],
                 ['name' => 'B37E56B2355AF43', 'origin' => 'suppliers_id', 'ref' => 'glpi_suppliers'],
             ],
             'glpi_changes_tickets' => [
                 ['name' => 'EBBABDAA5D80B7AB', 'origin' => 'changes_id', 'ref' => 'glpi_changes'],
                 ['name' => 'EBBABDAA8FDC0E9A', 'origin' => 'tickets_id', 'ref' => 'glpi_tickets'],
             ],
             'glpi_changes_users' => [
                 ['name' => '8C551D575D80B7AB', 'origin' => 'changes_id', 'ref' => 'glpi_changes'],
                 ['name' => '8C551D5767B3B43D', 'origin' => 'users_id', 'ref' => 'glpi_users'],
             ],
             'glpi_changetasks' => [
                 ['name' => '70399F55D80B7AB', 'origin' => 'changes_id', 'ref' => 'glpi_changes'],
                 ['name' => '70399F530D85233', 'origin' => 'taskcategories_id', 'ref' => 'glpi_taskcategories'],
                 ['name' => '70399F567B3B43D', 'origin' => 'users_id', 'ref' => 'glpi_users'],
                 ['name' => '70399F58CBB3EB6', 'origin' => 'users_id_editor', 'ref' => 'glpi_users'],
                 ['name' => '70399F5FD9C58DA', 'origin' => 'users_id_tech', 'ref' => 'glpi_users'],
                 ['name' => '70399F51421F0A5', 'origin' => 'groups_id_tech', 'ref' => 'glpi_groups'],
                 ['name' => '70399F53A064358', 'origin' => 'tasktemplates_id', 'ref' => 'glpi_tasktemplates'],
             ],
             'glpi_changetemplatehiddenfields' => [
                 ['name' => '71817FF964105530', 'origin' => 'changetemplates_id', 'ref' => 'glpi_changetemplates'],
             ],
             'glpi_changetemplatemandatoryfields' => [
                 ['name' => '45BDDDE264105530', 'origin' => 'changetemplates_id', 'ref' => 'glpi_changetemplates'],
             ],
             'glpi_changetemplatepredefinedfields' => [
                 ['name' => 'ECC634F464105530', 'origin' => 'changetemplates_id', 'ref' => 'glpi_changetemplates'],
             ],
             'glpi_changevalidations' => [
                 ['name' => 'C158C9467B3B43D', 'origin' => 'users_id', 'ref' => 'glpi_users'],
                 ['name' => 'C158C945D80B7AB', 'origin' => 'changes_id', 'ref' => 'glpi_changes'],
                 ['name' => 'C158C94E57CE233', 'origin' => 'users_id_validate', 'ref' => 'glpi_users'],
             ],
             'glpi_clusters' => [
                 ['name' => 'A63CCAB5FD9C58DA', 'origin' => 'users_id_tech', 'ref' => 'glpi_users'],
                 ['name' => 'A63CCAB51421F0A5', 'origin' => 'groups_id_tech', 'ref' => 'glpi_groups'],
                 ['name' => 'A63CCAB5B17973F', 'origin' => 'states_id', 'ref' => 'glpi_states'],
                 ['name' => 'A63CCAB59A4747C2', 'origin' => 'clustertypes_id', 'ref' => 'glpi_clustertypes'],
                 ['name' => 'A63CCAB5357A7B6F', 'origin' => 'autoupdatesystems_id', 'ref' => 'glpi_autoupdatesystems'],
             ],
             'glpi_computerantiviruses' => [
                 ['name' => '68671079F4B903A6', 'origin' => 'computers_id', 'ref' => 'glpi_computers'],
                 ['name' => '68671079A2A4C2E4', 'origin' => 'manufacturers_id', 'ref' => 'glpi_manufacturers'],
             ],
             'glpi_computers' => [
                 ['name' => '293E8ED8FD9C58DA', 'origin' => 'users_id_tech', 'ref' => 'glpi_users'],
                 ['name' => '293E8ED81421F0A5', 'origin' => 'groups_id_tech', 'ref' => 'glpi_groups'],
                 ['name' => '293E8ED8ED775E23', 'origin' => 'locations_id', 'ref' => 'glpi_locations'],
                 ['name' => '293E8ED8604D0C7C', 'origin' => 'networks_id', 'ref' => 'glpi_networks'],
                 ['name' => '293E8ED8604E2302', 'origin' => 'autoupdatesystems_id', 'ref' => 'glpi_autoupdatesystems'],
                 ['name' => '293E8ED866A32204', 'origin' => 'computermodels_id', 'ref' => 'glpi_computermodels'],
                 ['name' => '293E8ED89B4E6864', 'origin' => 'computertypes_id', 'ref' => 'glpi_computertypes'],
                 ['name' => '293E8ED8A2A4C2E4', 'origin' => 'manufacturers_id', 'ref' => 'glpi_manufacturers'],
                 ['name' => '293E8ED867B3B43D', 'origin' => 'users_id', 'ref' => 'glpi_users'],
                 ['name' => '293E8ED8F373DCF', 'origin' => 'groups_id', 'ref' => 'glpi_groups'],
                 ['name' => '293E8ED8B17973F', 'origin' => 'states_id', 'ref' => 'glpi_states'],
             ],
             'glpi_computers_items' => [
                 ['name' => 'BCF5679DF4B903A6', 'origin' => 'computers_id', 'ref' => 'glpi_computers'],
             ],
             'glpi_computervirtualmachines' => [
                 ['name' => '6FDC320CF4B903A6', 'origin' => 'computers_id', 'ref' => 'glpi_computers'],
                 ['name' => '6FDC320C9280C5B3', 'origin' => 'virtualmachinestates_id', 'ref' => 'glpi_virtualmachinestates'],
                 ['name' => '6FDC320CA58A2734', 'origin' => 'virtualmachinesystems_id', 'ref' => 'glpi_virtualmachinesystems'],
                 ['name' => '6FDC320C10B10554', 'origin' => 'virtualmachinetypes_id', 'ref' => 'glpi_virtualmachinetypes'],
             ],
             'glpi_consumableitems' => [
                 ['name' => 'B83ADB4AED775E23', 'origin' => 'locations_id', 'ref' => 'glpi_locations'],
                 ['name' => 'B83ADB4A1F067AC9', 'origin' => 'consumableitemtypes_id', 'ref' => 'glpi_consumableitemtypes'],
                 ['name' => 'B83ADB4AA2A4C2E4', 'origin' => 'manufacturers_id', 'ref' => 'glpi_manufacturers'],
                 ['name' => 'B83ADB4AFD9C58DA', 'origin' => 'users_id_tech', 'ref' => 'glpi_users'],
                 ['name' => 'B83ADB4A1421F0A5', 'origin' => 'groups_id_tech', 'ref' => 'glpi_groups'],
             ],
             'glpi_consumables' => [
                 ['name' => 'F28618F29E324C33', 'origin' => 'consumableitems_id', 'ref' => 'glpi_consumableitems'],
             ],
             'glpi_contacts' => [
                 ['name' => '79F582F960D5F3AB', 'origin' => 'contacttypes_id', 'ref' => 'glpi_contacttypes'],
                 ['name' => '79F582F99CE64CF3', 'origin' => 'usertitles_id', 'ref' => 'glpi_usertitles'],
             ],
             'glpi_contacts_suppliers' => [
                 ['name' => '8B35180D355AF43', 'origin' => 'suppliers_id', 'ref' => 'glpi_suppliers'],
                 ['name' => '8B35180D719FB48E', 'origin' => 'contacts_id', 'ref' => 'glpi_contacts'],
             ],
             'glpi_contractcosts' => [
                 ['name' => '888F838124584564', 'origin' => 'contracts_id', 'ref' => 'glpi_contracts'],
                 ['name' => '888F838122FD2D3D', 'origin' => 'budgets_id', 'ref' => 'glpi_budgets'],
             ],
             'glpi_contracts' => [
                 ['name' => '47776DAF2ABAFE2', 'origin' => 'contracttypes_id', 'ref' => 'glpi_contracttypes'],
                 ['name' => '47776DAB17973F', 'origin' => 'states_id', 'ref' => 'glpi_states'],
             ],
             'glpi_contracts_items' => [
                 ['name' => '5FF01F0E24584564', 'origin' => 'contracts_id', 'ref' => 'glpi_contracts'],
             ],
             'glpi_contracts_suppliers' => [
                 ['name' => '78E40104355AF43', 'origin' => 'suppliers_id', 'ref' => 'glpi_suppliers'],
                 ['name' => '78E4010424584564', 'origin' => 'contracts_id', 'ref' => 'glpi_contracts'],
             ],
             'glpi_crontasklogs' => [
                 ['name' => 'F45D647FB01F6436', 'origin' => 'crontasks_id', 'ref' => 'glpi_crontasks'],
                 ['name' => 'F45D647F2D2CC539', 'origin' => 'crontasklogs_id', 'ref' => 'glpi_crontasklogs'],
             ],
             'glpi_dashboards' => [
                 ['name' => '7331D499B26949C', 'origin' => 'profileId', 'ref' => 'glpi_profiles'],
                 ['name' => '7331D4964B64DCC', 'origin' => 'userId', 'ref' => 'glpi_users'],
             ],
             'glpi_datacenters' => [
                 ['name' => 'D729C869ED775E23', 'origin' => 'locations_id', 'ref' => 'glpi_locations'],
             ],
             'glpi_dcrooms' => [
                 ['name' => 'BC44EC93ED775E23', 'origin' => 'locations_id', 'ref' => 'glpi_locations'],
                 ['name' => 'BC44EC93EB7A8A62', 'origin' => 'datacenters_id', 'ref' => 'glpi_datacenters'],
             ],
             'glpi_devicebatteries' => [
                 ['name' => 'A652C99DA2A4C2E4', 'origin' => 'manufacturers_id', 'ref' => 'glpi_manufacturers'],
                 ['name' => 'A652C99D6F236F43', 'origin' => 'devicebatterytypes_id', 'ref' => 'glpi_devicebatterytypes'],
                 ['name' => 'A652C99DC35DFA68', 'origin' => 'devicebatterymodels_id', 'ref' => 'glpi_devicebatterymodels'],
             ],
             'glpi_devicecases' => [
                 ['name' => 'A1AE63687964C119', 'origin' => 'devicecasetypes_id', 'ref' => 'glpi_devicecasetypes'],
                 ['name' => 'A1AE6368A2A4C2E4', 'origin' => 'manufacturers_id', 'ref' => 'glpi_manufacturers'],
                 ['name' => 'A1AE636848F5052C', 'origin' => 'devicecasemodels_id', 'ref' => 'glpi_devicecasemodels'],
             ],
             'glpi_devicecontrols' => [
                 ['name' => '8FBFCEDFA2A4C2E4', 'origin' => 'manufacturers_id', 'ref' => 'glpi_manufacturers'],
                 ['name' => '8FBFCEDFD08D9B0', 'origin' => 'interfacetypes_id', 'ref' => 'glpi_interfacetypes'],
                 ['name' => '8FBFCEDFE8A65268', 'origin' => 'devicecontrolmodels_id', 'ref' => 'glpi_devicecontrolmodels'],
             ],
             'glpi_devicedrives' => [
                 ['name' => '5FDF4AEDA2A4C2E4', 'origin' => 'manufacturers_id', 'ref' => 'glpi_manufacturers'],
                 ['name' => '5FDF4AEDD08D9B0', 'origin' => 'interfacetypes_id', 'ref' => 'glpi_interfacetypes'],
                 ['name' => '5FDF4AED6B59CD4B', 'origin' => 'devicedrivemodels_id', 'ref' => 'glpi_devicedrivemodels'],
             ],
             'glpi_devicefirmwares' => [
                 ['name' => '27AD954FA2A4C2E4', 'origin' => 'manufacturers_id', 'ref' => 'glpi_manufacturers'],
                 ['name' => '27AD954FFE693480', 'origin' => 'devicefirmwaretypes_id', 'ref' => 'glpi_devicefirmwaretypes'],
                 ['name' => '27AD954FC1A12339', 'origin' => 'devicefirmwaremodels_id', 'ref' => 'glpi_devicefirmwaremodels'],
             ],
             'glpi_devicegenerics' => [
                 ['name' => '711041243D805C04', 'origin' => 'devicegenerictypes_id', 'ref' => 'glpi_devicegenerictypes'],
                 ['name' => '71104124A2A4C2E4', 'origin' => 'manufacturers_id', 'ref' => 'glpi_manufacturers'],
                 ['name' => '71104124ED775E23', 'origin' => 'locations_id', 'ref' => 'glpi_locations'],
                 ['name' => '71104124B17973F', 'origin' => 'states_id', 'ref' => 'glpi_states'],
                 ['name' => '711041242BB78D68', 'origin' => 'devicegenericmodels_id', 'ref' => 'glpi_devicegenericmodels'],
             ],
             'glpi_devicegraphiccards' => [
                 ['name' => '13F4C69ED08D9B0', 'origin' => 'interfacetypes_id', 'ref' => 'glpi_interfacetypes'],
                 ['name' => '13F4C69EA2A4C2E4', 'origin' => 'manufacturers_id', 'ref' => 'glpi_manufacturers'],
                 ['name' => '13F4C69EE40E4F0B', 'origin' => 'devicegraphiccardmodels_id', 'ref' => 'glpi_devicegraphiccardmodels'],
             ],
             'glpi_deviceharddrives' => [
                 ['name' => 'EA210CE0D08D9B0', 'origin' => 'interfacetypes_id', 'ref' => 'glpi_interfacetypes'],
                 ['name' => 'EA210CE0A2A4C2E4', 'origin' => 'manufacturers_id', 'ref' => 'glpi_manufacturers'],
                 ['name' => 'EA210CE06BF9C8E6', 'origin' => 'deviceharddrivemodels_id', 'ref' => 'glpi_deviceharddrivemodels'],
             ],
             'glpi_devicememories' => [
                 ['name' => '7AAE9065A2A4C2E4', 'origin' => 'manufacturers_id', 'ref' => 'glpi_manufacturers'],
                 ['name' => '7AAE9065974BFB7E', 'origin' => 'devicememorytypes_id', 'ref' => 'glpi_devicememorytypes'],
                 ['name' => '7AAE90659BCDDEED', 'origin' => 'devicememorymodels_id', 'ref' => 'glpi_devicememorymodels'],
             ],
             'glpi_devicemotherboards' => [
                 ['name' => 'BA4EEEB7A2A4C2E4', 'origin' => 'manufacturers_id', 'ref' => 'glpi_manufacturers'],
                 ['name' => 'BA4EEEB738038C79', 'origin' => 'devicemotherboardmodels_id', 'ref' => 'glpi_devicemotherboardmodels'],
             ],
             'glpi_devicenetworkcards' => [
                 ['name' => '2F394962A2A4C2E4', 'origin' => 'manufacturers_id', 'ref' => 'glpi_manufacturers'],
                 ['name' => '2F394962FB338CF8', 'origin' => 'devicenetworkcardmodels_id', 'ref' => 'glpi_devicenetworkcardmodels'],
             ],
             'glpi_devicepcis' => [
                 ['name' => '754B0561A2A4C2E4', 'origin' => 'manufacturers_id', 'ref' => 'glpi_manufacturers'],
                 ['name' => '754B0561FB338CF8', 'origin' => 'devicenetworkcardmodels_id', 'ref' => 'glpi_devicenetworkcardmodels'],
                 ['name' => '754B0561A809D5C7', 'origin' => 'devicepcimodels_id', 'ref' => 'glpi_devicepcimodels'],
             ],
             'glpi_devicepowersupplies' => [
                 ['name' => '7C7209EDA2A4C2E4', 'origin' => 'manufacturers_id', 'ref' => 'glpi_manufacturers'],
                 ['name' => '7C7209ED1DECE3C7', 'origin' => 'devicepowersupplymodels_id', 'ref' => 'glpi_devicepowersupplymodels'],
             ],
             'glpi_deviceprocessors' => [
                 ['name' => 'B6E0F8BBA2A4C2E4', 'origin' => 'manufacturers_id', 'ref' => 'glpi_manufacturers'],
                 ['name' => 'B6E0F8BBD4D3D667', 'origin' => 'deviceprocessormodels_id', 'ref' => 'glpi_deviceprocessormodels'],
             ],
             'glpi_devicesensors' => [
                 ['name' => 'E8328652B91582EB', 'origin' => 'devicesensortypes_id', 'ref' => 'glpi_devicesensortypes'],
                 ['name' => 'E83286521B86E75F', 'origin' => 'devicesensormodels_id', 'ref' => 'glpi_devicesensormodels'],
                 ['name' => 'E8328652A2A4C2E4', 'origin' => 'manufacturers_id', 'ref' => 'glpi_manufacturers'],
                 ['name' => 'E8328652ED775E23', 'origin' => 'locations_id', 'ref' => 'glpi_locations'],
                 ['name' => 'E8328652B17973F', 'origin' => 'states_id', 'ref' => 'glpi_states'],
             ],
             'glpi_devicesimcards' => [
                 ['name' => '5A0BB1A8A2A4C2E4', 'origin' => 'manufacturers_id', 'ref' => 'glpi_manufacturers'],
                 ['name' => '5A0BB1A87A74B37', 'origin' => 'devicesimcardtypes_id', 'ref' => 'glpi_devicesimcardtypes'],
             ],
             'glpi_devicesoundcards' => [
                 ['name' => 'D53EF47AA2A4C2E4', 'origin' => 'manufacturers_id', 'ref' => 'glpi_manufacturers'],
                 ['name' => 'D53EF47A53D98B78', 'origin' => 'devicesoundcardmodels_id', 'ref' => 'glpi_devicesoundcardmodels'],
             ],
             'glpi_displaypreferences' => [
                 ['name' => '67F2BE767B3B43D', 'origin' => 'users_id', 'ref' => 'glpi_users'],
             ],
             'glpi_documentcategories' => [
                 ['name' => '44E98B1F55AC576F', 'origin' => 'documentcategories_id', 'ref' => 'glpi_documentcategories'],
             ],
             'glpi_documents' => [
                 ['name' => 'AF97AD2155AC576F', 'origin' => 'documentcategories_id', 'ref' => 'glpi_documentcategories'],
                 ['name' => 'AF97AD2167B3B43D', 'origin' => 'users_id', 'ref' => 'glpi_users'],
                 ['name' => 'AF97AD218FDC0E9A', 'origin' => 'tickets_id', 'ref' => 'glpi_tickets'],
             ],
             'glpi_documents_items' => [
                 ['name' => 'DDD24B255F0F2752', 'origin' => 'documents_id', 'ref' => 'glpi_documents'],
                 ['name' => 'DDD24B2567B3B43D', 'origin' => 'users_id', 'ref' => 'glpi_users'],
             ],
             'glpi_domainrecords' => [
                 ['name' => '180F59563700F4DC', 'origin' => 'domains_id', 'ref' => 'glpi_domains'],
                 ['name' => '180F595671E56292', 'origin' => 'domainrecordtypes_id', 'ref' => 'glpi_domainrecordtypes'],
                 ['name' => '180F5956FD9C58DA', 'origin' => 'users_id_tech', 'ref' => 'glpi_users'],
                 ['name' => '180F59561421F0A5', 'origin' => 'groups_id_tech', 'ref' => 'glpi_groups'],
             ],
             'glpi_domains' => [
                 ['name' => 'E64974F9182DCB32', 'origin' => 'domaintypes_id', 'ref' => 'glpi_domaintypes'],
                 ['name' => 'E64974F9FD9C58DA', 'origin' => 'users_id_tech', 'ref' => 'glpi_users'],
                 ['name' => 'E64974F91421F0A5', 'origin' => 'groups_id_tech', 'ref' => 'glpi_groups'],
             ],
             'glpi_domains_items' => [
                 ['name' => '1E9AB0AA3700F4DC', 'origin' => 'domains_id', 'ref' => 'glpi_domains'],
                 ['name' => '1E9AB0AA4360E635', 'origin' => 'domainrelations_id', 'ref' => 'glpi_domainrelations'],
             ],
             'glpi_enclosures' => [
                 ['name' => '6052344AED775E23', 'origin' => 'locations_id', 'ref' => 'glpi_locations'],
                 ['name' => '6052344A8ED84E80', 'origin' => 'enclosuremodels_id', 'ref' => 'glpi_enclosuremodels'],
                 ['name' => '6052344AFD9C58DA', 'origin' => 'users_id_tech', 'ref' => 'glpi_users'],
                 ['name' => '6052344A1421F0A5', 'origin' => 'groups_id_tech', 'ref' => 'glpi_groups'],
                 ['name' => '6052344AB17973F', 'origin' => 'states_id', 'ref' => 'glpi_states'],
                 ['name' => '6052344AA2A4C2E4', 'origin' => 'manufacturers_id', 'ref' => 'glpi_manufacturers'],
             ],
             'glpi_entities' => [
                 ['name' => '1A59F36F7D03EC85', 'origin' => 'authldaps_id', 'ref' => 'glpi_authldaps'],
                 ['name' => '1A59F36F72C4B705', 'origin' => 'calendars_id', 'ref' => 'glpi_calendars'],
                 ['name' => '1A59F36F10E3E815', 'origin' => 'tickettemplates_id', 'ref' => 'glpi_tickettemplates'],
                 ['name' => '1A59F36F64105530', 'origin' => 'changetemplates_id', 'ref' => 'glpi_changetemplates'],
                 ['name' => '1A59F36F7A8D7635', 'origin' => 'problemtemplates_id', 'ref' => 'glpi_problemtemplates'],
                 ['name' => '1A59F36F16010B6F', 'origin' => 'entities_id_software', 'ref' => 'glpi_entities'],
             ],
             'glpi_entities_knowbaseitems' => [
                 ['name' => '30391006D89C108', 'origin' => 'knowbaseitems_id', 'ref' => 'glpi_knowbaseitems'],
             ],
             'glpi_entities_reminders' => [
                 ['name' => '265E9306C7C7BF28', 'origin' => 'reminders_id', 'ref' => 'glpi_reminders'],
             ],
             'glpi_entities_rssfeeds' => [
                 ['name' => '8F946B4A2920D1F', 'origin' => 'rssfeeds_id', 'ref' => 'glpi_rssfeeds'],
             ],
             'glpi_groups' => [
                 ['name' => '7286AF61F373DCF', 'origin' => 'groups_id', 'ref' => 'glpi_groups'],
             ],
             'glpi_groups_knowbaseitems' => [
                 ['name' => '9F9797EA6D89C108', 'origin' => 'knowbaseitems_id', 'ref' => 'glpi_knowbaseitems'],
                 ['name' => '9F9797EAF373DCF', 'origin' => 'groups_id', 'ref' => 'glpi_groups'],
             ],
             'glpi_groups_problems' => [
                 ['name' => '35FF34E0E30A47DD', 'origin' => 'problems_id', 'ref' => 'glpi_problems'],
                 ['name' => '35FF34E0F373DCF', 'origin' => 'groups_id', 'ref' => 'glpi_groups'],
             ],
             'glpi_groups_reminders' => [
                 ['name' => 'CB9577E5C7C7BF28', 'origin' => 'reminders_id', 'ref' => 'glpi_reminders'],
                 ['name' => 'CB9577E5F373DCF', 'origin' => 'groups_id', 'ref' => 'glpi_groups'],
             ],
             'glpi_groups_rssfeeds' => [
                 ['name' => 'FCF3A8CA2920D1F', 'origin' => 'rssfeeds_id', 'ref' => 'glpi_rssfeeds'],
                 ['name' => 'FCF3A8CAF373DCF', 'origin' => 'groups_id', 'ref' => 'glpi_groups'],
             ],
             'glpi_groups_tickets' => [
                 ['name' => 'C6573B418FDC0E9A', 'origin' => 'tickets_id', 'ref' => 'glpi_tickets'],
                 ['name' => 'C6573B41F373DCF', 'origin' => 'groups_id', 'ref' => 'glpi_groups'],
             ],
             'glpi_groups_users' => [
                 ['name' => '3023C81467B3B43D', 'origin' => 'users_id', 'ref' => 'glpi_users'],
                 ['name' => '3023C814F373DCF', 'origin' => 'groups_id', 'ref' => 'glpi_groups'],
             ],
             'glpi_infocoms' => [
                 ['name' => '8D32298C355AF43', 'origin' => 'suppliers_id', 'ref' => 'glpi_suppliers'],
                 ['name' => '8D32298C22FD2D3D', 'origin' => 'budgets_id', 'ref' => 'glpi_budgets'],
                 ['name' => '8D32298CFCE88FAB', 'origin' => 'businesscriticities_id', 'ref' => 'glpi_businesscriticities'],
             ],
             'glpi_ipaddresses_ipnetworks' => [
                 ['name' => '107118A965020FC5', 'origin' => 'ipaddresses_id', 'ref' => 'glpi_ipaddresses'],
                 ['name' => '107118A9A992AA50', 'origin' => 'ipnetworks_id', 'ref' => 'glpi_ipnetworks'],
             ],
             'glpi_ipnetworks' => [
                 ['name' => '2D47D3C8A992AA50', 'origin' => 'ipnetworks_id', 'ref' => 'glpi_ipnetworks'],
             ],
             'glpi_ipnetworks_vlans' => [
                 ['name' => '35A7AD8AA992AA50', 'origin' => 'ipnetworks_id', 'ref' => 'glpi_ipnetworks'],
                 ['name' => '35A7AD8A462B676C', 'origin' => 'vlans_id', 'ref' => 'glpi_vlans'],
             ],
             'glpi_itilcategories' => [
                 ['name' => '349D19C4EFE9C34D', 'origin' => 'itilcategories_id', 'ref' => 'glpi_itilcategories'],
                 ['name' => '349D19C4551BC90F', 'origin' => 'knowbaseitemcategories_id', 'ref' => 'glpi_knowbaseitemcategories'],
                 ['name' => '349D19C467B3B43D', 'origin' => 'users_id', 'ref' => 'glpi_users'],
                 ['name' => '349D19C4F373DCF', 'origin' => 'groups_id', 'ref' => 'glpi_groups'],
                 ['name' => '349D19C4943F6381', 'origin' => 'tickettemplates_id_incident', 'ref' => 'glpi_tickettemplates'],
                 ['name' => '349D19C44B225EE6', 'origin' => 'tickettemplates_id_demand', 'ref' => 'glpi_tickettemplates'],
                 ['name' => '349D19C464105530', 'origin' => 'changetemplates_id', 'ref' => 'glpi_changetemplates'],
                 ['name' => '349D19C47A8D7635', 'origin' => 'problemtemplates_id', 'ref' => 'glpi_problemtemplates'],
             ],
             'glpi_itilfollowups' => [
                 ['name' => '1FCFA25D67B3B43D', 'origin' => 'users_id', 'ref' => 'glpi_users'],
                 ['name' => '1FCFA25D8CBB3EB6', 'origin' => 'users_id_editor', 'ref' => 'glpi_users'],
                 ['name' => '1FCFA25DD0DEA07D', 'origin' => 'requesttypes_id', 'ref' => 'glpi_requesttypes'],
             ],
             'glpi_itilfollowuptemplates' => [
                 ['name' => '3934C13BD0DEA07D', 'origin' => 'requesttypes_id', 'ref' => 'glpi_requesttypes'],
             ],
             'glpi_itils_projects' => [
                 ['name' => '64C0EB2B1EDE0F55', 'origin' => 'projects_id', 'ref' => 'glpi_projects'],
             ],
             'glpi_itilsolutions' => [
                 ['name' => 'BF4769765E58E090', 'origin' => 'solutiontypes_id', 'ref' => 'glpi_solutiontypes'],
                 ['name' => 'BF47697667B3B43D', 'origin' => 'users_id', 'ref' => 'glpi_users'],
                 ['name' => 'BF4769768CBB3EB6', 'origin' => 'users_id_editor', 'ref' => 'glpi_users'],
                 ['name' => 'BF476976B18E454C', 'origin' => 'users_id_approval', 'ref' => 'glpi_users'],
                 ['name' => 'BF476976251F6A08', 'origin' => 'itilfollowups_id', 'ref' => 'glpi_itilfollowups'],
             ],
             'glpi_knowbaseitems' => [
                 ['name' => '2E07C924551BC90F', 'origin' => 'knowbaseitemcategories_id', 'ref' => 'glpi_knowbaseitemcategories'],
                 ['name' => '2E07C92467B3B43D', 'origin' => 'users_id', 'ref' => 'glpi_users'],
             ],
             'glpi_knowbaseitems_comments' => [
                 ['name' => '33AB06316D89C108', 'origin' => 'knowbaseitems_id', 'ref' => 'glpi_knowbaseitems'],
                 ['name' => '33AB063167B3B43D', 'origin' => 'users_id', 'ref' => 'glpi_users'],
             ],
             'glpi_knowbaseitems_profiles' => [
                 ['name' => 'E705152B6D89C108', 'origin' => 'knowbaseitems_id', 'ref' => 'glpi_knowbaseitems'],
                 ['name' => 'E705152B22077C89', 'origin' => 'profiles_id', 'ref' => 'glpi_profiles'],
             ],
             'glpi_knowbaseitems_revisions' => [
                 ['name' => '3B8DEF96D89C108', 'origin' => 'knowbaseitems_id', 'ref' => 'glpi_knowbaseitems'],
             ],
             'glpi_knowbaseitems_users' => [
                 ['name' => '4987D7AC6D89C108', 'origin' => 'knowbaseitems_id', 'ref' => 'glpi_knowbaseitems'],
                 ['name' => '4987D7AC67B3B43D', 'origin' => 'users_id', 'ref' => 'glpi_users'],
             ],
             'glpi_knowbaseitemtranslations' => [
                 ['name' => 'BF433A936D89C108', 'origin' => 'knowbaseitems_id', 'ref' => 'glpi_knowbaseitems'],
                 ['name' => 'BF433A9367B3B43D', 'origin' => 'users_id', 'ref' => 'glpi_users'],
             ],
             'glpi_lines' => [
                 ['name' => 'AC635CC067B3B43D', 'origin' => 'users_id', 'ref' => 'glpi_users'],
                 ['name' => 'AC635CC0F373DCF', 'origin' => 'groups_id', 'ref' => 'glpi_groups'],
                 ['name' => 'AC635CC0C12D38F2', 'origin' => 'lineoperators_id', 'ref' => 'glpi_lineoperators'],
                 ['name' => 'AC635CC0ED775E23', 'origin' => 'locations_id', 'ref' => 'glpi_locations'],
                 ['name' => 'AC635CC0B17973F', 'origin' => 'states_id', 'ref' => 'glpi_states'],
                 ['name' => 'AC635CC04F5E534D', 'origin' => 'linetypes_id', 'ref' => 'glpi_linetypes'],
             ],
             'glpi_locations' => [
                 ['name' => '1AC19513ED775E23', 'origin' => 'locations_id', 'ref' => 'glpi_locations'],
             ],
             'glpi_monitors' => [
                 ['name' => 'CC883AB7FD9C58DA', 'origin' => 'users_id_tech', 'ref' => 'glpi_users'],
                 ['name' => 'CC883AB71421F0A5', 'origin' => 'groups_id_tech', 'ref' => 'glpi_groups'],
                 ['name' => 'CC883AB7ED775E23', 'origin' => 'locations_id', 'ref' => 'glpi_locations'],
                 ['name' => 'CC883AB7A996F641', 'origin' => 'monitortypes_id', 'ref' => 'glpi_monitortypes'],
                 ['name' => 'CC883AB72D952EDD', 'origin' => 'monitormodels_id', 'ref' => 'glpi_monitormodels'],
                 ['name' => 'CC883AB7A2A4C2E4', 'origin' => 'manufacturers_id', 'ref' => 'glpi_manufacturers'],
                 ['name' => 'CC883AB767B3B43D', 'origin' => 'users_id', 'ref' => 'glpi_users'],
                 ['name' => 'CC883AB7F373DCF', 'origin' => 'groups_id', 'ref' => 'glpi_groups'],
                 ['name' => 'CC883AB7B17973F', 'origin' => 'states_id', 'ref' => 'glpi_states'],
             ],
             'glpi_netpoints' => [
                 ['name' => '69DBE45CED775E23', 'origin' => 'locations_id', 'ref' => 'glpi_locations'],
             ],
             'glpi_networkaliases' => [
                 ['name' => '4F9E21DC584BEB4F', 'origin' => 'networknames_id', 'ref' => 'glpi_networknames'],
                 ['name' => '4F9E21DC6C543AFA', 'origin' => 'fqdns_id', 'ref' => 'glpi_fqdns'],
             ],
             'glpi_networkequipments' => [
                 ['name' => 'AFE59A84FD9C58DA', 'origin' => 'users_id_tech', 'ref' => 'glpi_users'],
                 ['name' => 'AFE59A841421F0A5', 'origin' => 'groups_id_tech', 'ref' => 'glpi_groups'],
                 ['name' => 'AFE59A84ED775E23', 'origin' => 'locations_id', 'ref' => 'glpi_locations'],
                 ['name' => 'AFE59A84604D0C7C', 'origin' => 'networks_id', 'ref' => 'glpi_networks'],
                 ['name' => 'AFE59A8473C51A8B', 'origin' => 'networkequipmenttypes_id', 'ref' => 'glpi_networkequipmenttypes'],
                 ['name' => 'AFE59A8456FE569F', 'origin' => 'networkequipmentmodels_id', 'ref' => 'glpi_networkequipmentmodels'],
                 ['name' => 'AFE59A84A2A4C2E4', 'origin' => 'manufacturers_id', 'ref' => 'glpi_manufacturers'],
                 ['name' => 'AFE59A8467B3B43D', 'origin' => 'users_id', 'ref' => 'glpi_users'],
                 ['name' => 'AFE59A84F373DCF', 'origin' => 'groups_id', 'ref' => 'glpi_groups'],
                 ['name' => 'AFE59A84B17973F', 'origin' => 'states_id', 'ref' => 'glpi_states'],
             ],
             'glpi_networknames' => [
                 ['name' => 'A148F0756C543AFA', 'origin' => 'fqdns_id', 'ref' => 'glpi_fqdns'],
             ],
             'glpi_networkportaggregates' => [
                 ['name' => '88867CD3CE45BD77', 'origin' => 'networkports_id', 'ref' => 'glpi_networkports'],
                 ['name' => '88867CD39E2F9770', 'origin' => 'networkports_id_list', 'ref' => 'glpi_networkports'],
             ],
             'glpi_networkportaliases' => [
                 ['name' => '1ADCE793CE45BD77', 'origin' => 'networkports_id', 'ref' => 'glpi_networkports'],
                 ['name' => '1ADCE793A2DF6591', 'origin' => 'networkports_id_alias', 'ref' => 'glpi_networkports'],
             ],
             'glpi_networkportdialups' => [
                 ['name' => 'E90B503DCE45BD77', 'origin' => 'networkports_id', 'ref' => 'glpi_networkports'],
             ],
             'glpi_networkportethernets' => [
                 ['name' => '9A1A7916CE45BD77', 'origin' => 'networkports_id', 'ref' => 'glpi_networkports'],
                 ['name' => '9A1A79165DF72560', 'origin' => 'netpoints_id', 'ref' => 'glpi_netpoints'],
             ],
             'glpi_networkportfiberchannels' => [
                 ['name' => 'C62BE585CE45BD77', 'origin' => 'networkports_id', 'ref' => 'glpi_networkports'],
                 ['name' => 'C62BE5855DF72560', 'origin' => 'netpoints_id', 'ref' => 'glpi_netpoints'],
             ],
             'glpi_networkportlocals' => [
                 ['name' => 'A454ACE4CE45BD77', 'origin' => 'networkports_id', 'ref' => 'glpi_networkports'],
             ],
             'glpi_networkports_networkports' => [
                 ['name' => 'DF0512CAF88ABD1B', 'origin' => 'networkports_id_1', 'ref' => 'glpi_networkports'],
                 ['name' => 'DF0512CA6183ECA1', 'origin' => 'networkports_id_2', 'ref' => 'glpi_networkports'],
             ],
             'glpi_networkports_vlans' => [
                 ['name' => '84FF692CCE45BD77', 'origin' => 'networkports_id', 'ref' => 'glpi_networkports'],
                 ['name' => '84FF692C462B676C', 'origin' => 'vlans_id', 'ref' => 'glpi_vlans'],
             ],
             'glpi_networkportwifis' => [
                 ['name' => 'FB43456ACE45BD77', 'origin' => 'networkports_id', 'ref' => 'glpi_networkports'],
                 ['name' => 'FB43456A782248B2', 'origin' => 'wifinetworks_id', 'ref' => 'glpi_wifinetworks'],
                 ['name' => 'FB43456A4D4D852B', 'origin' => 'networkportwifis_id', 'ref' => 'glpi_networkportwifis'],
             ],
             'glpi_notepads' => [
                 ['name' => 'BCDEFE2267B3B43D', 'origin' => 'users_id', 'ref' => 'glpi_users'],
                 ['name' => 'BCDEFE2227D112BD', 'origin' => 'users_id_lastupdater', 'ref' => 'glpi_users'],
             ],
             'glpi_notifications_notificationtemplates' => [
                 ['name' => '45FE608ED4BE081', 'origin' => 'notifications_id', 'ref' => 'glpi_notifications'],
                 ['name' => '45FE608EA9E8DD2B', 'origin' => 'notificationtemplates_id', 'ref' => 'glpi_notificationtemplates'],
             ],
             'glpi_notificationtargets' => [
                 ['name' => '9E40A2B1D4BE081', 'origin' => 'notifications_id', 'ref' => 'glpi_notifications'],
             ],
             'glpi_notificationtemplatetranslations' => [
                 ['name' => '8F8C3CD6A9E8DD2B', 'origin' => 'notificationtemplates_id', 'ref' => 'glpi_notificationtemplates'],
             ],
             'glpi_notimportedemails' => [
                 ['name' => '36514841F9E7A2C', 'origin' => 'mailcollectors_id', 'ref' => 'glpi_mailcollectors'],
                 ['name' => '365148467B3B43D', 'origin' => 'users_id', 'ref' => 'glpi_users'],
             ],
             'glpi_objectlocks' => [
                 ['name' => '55A8E45D67B3B43D', 'origin' => 'users_id', 'ref' => 'glpi_users'],
             ],
             'glpi_olalevelactions' => [
                 ['name' => '4ABB859EC6D702C', 'origin' => 'olalevels_id', 'ref' => 'glpi_olalevels'],
             ],
             'glpi_olalevelcriterias' => [
                 ['name' => 'E04BD147C6D702C', 'origin' => 'olalevels_id', 'ref' => 'glpi_olalevels'],
             ],
             'glpi_olalevels' => [
                 ['name' => 'EC99B26DDB7C61FE', 'origin' => 'olas_id', 'ref' => 'glpi_olas'],
             ],
             'glpi_olalevels_tickets' => [
                 ['name' => 'B47FA3F8FDC0E9A', 'origin' => 'tickets_id', 'ref' => 'glpi_tickets'],
                 ['name' => 'B47FA3FC6D702C', 'origin' => 'olalevels_id', 'ref' => 'glpi_olalevels'],
             ],
             'glpi_olas' => [
                 ['name' => 'B7FD34E572C4B705', 'origin' => 'calendars_id', 'ref' => 'glpi_calendars'],
                 ['name' => 'B7FD34E5BEF27A45', 'origin' => 'slms_id', 'ref' => 'glpi_slms'],
             ],
             'glpi_operatingsystemkernelversions' => [
                 ['name' => '69A5AEB9340E0989', 'origin' => 'operatingsystemkernels_id', 'ref' => 'glpi_operatingsystemkernels'],
             ],
             'glpi_passivedcequipments' => [
                 ['name' => '3CF108C6ED775E23', 'origin' => 'locations_id', 'ref' => 'glpi_locations'],
                 ['name' => '3CF108C68DA5A79E', 'origin' => 'passivedcequipmentmodels_id', 'ref' => 'glpi_passivedcequipmentmodels'],
                 ['name' => '3CF108C693FDCDA1', 'origin' => 'passivedcequipmenttypes_id', 'ref' => 'glpi_passivedcequipmenttypes'],
                 ['name' => '3CF108C6FD9C58DA', 'origin' => 'users_id_tech', 'ref' => 'glpi_users'],
                 ['name' => '3CF108C61421F0A5', 'origin' => 'groups_id_tech', 'ref' => 'glpi_groups'],
                 ['name' => '3CF108C6B17973F', 'origin' => 'states_id', 'ref' => 'glpi_states'],
                 ['name' => '3CF108C6A2A4C2E4', 'origin' => 'manufacturers_id', 'ref' => 'glpi_manufacturers'],
             ],
             'glpi_pdus' => [
                 ['name' => '9F3AF5C1ED775E23', 'origin' => 'locations_id', 'ref' => 'glpi_locations'],
                 ['name' => '9F3AF5C1909D471A', 'origin' => 'pdumodels_id', 'ref' => 'glpi_pdumodels'],
                 ['name' => '9F3AF5C1FD9C58DA', 'origin' => 'users_id_tech', 'ref' => 'glpi_users'],
                 ['name' => '9F3AF5C11421F0A5', 'origin' => 'groups_id_tech', 'ref' => 'glpi_groups'],
                 ['name' => '9F3AF5C1B17973F', 'origin' => 'states_id', 'ref' => 'glpi_states'],
                 ['name' => '9F3AF5C1A2A4C2E4', 'origin' => 'manufacturers_id', 'ref' => 'glpi_manufacturers'],
                 ['name' => '9F3AF5C11C0D2DB1', 'origin' => 'pdutypes_id', 'ref' => 'glpi_pdutypes'],
             ],
             'glpi_pdus_plugs' => [
                 ['name' => '460B319C15134C17', 'origin' => 'plugs_id', 'ref' => 'glpi_plugs'],
                 ['name' => '460B319C33D93EF6', 'origin' => 'pdus_id', 'ref' => 'glpi_pdus'],
             ],
             'glpi_pdus_racks' => [
                 ['name' => '7ABF2AEF269E262D', 'origin' => 'racks_id', 'ref' => 'glpi_racks'],
                 ['name' => '7ABF2AEF33D93EF6', 'origin' => 'pdus_id', 'ref' => 'glpi_pdus'],
             ],
             'glpi_peripherals' => [
                 ['name' => 'B49D126FD9C58DA', 'origin' => 'users_id_tech', 'ref' => 'glpi_users'],
                 ['name' => 'B49D1261421F0A5', 'origin' => 'groups_id_tech', 'ref' => 'glpi_groups'],
                 ['name' => 'B49D126ED775E23', 'origin' => 'locations_id', 'ref' => 'glpi_locations'],
                 ['name' => 'B49D12682709FED', 'origin' => 'peripheraltypes_id', 'ref' => 'glpi_peripheraltypes'],
                 ['name' => 'B49D126F2DE2777', 'origin' => 'peripheralmodels_id', 'ref' => 'glpi_peripheralmodels'],
                 ['name' => 'B49D126A2A4C2E4', 'origin' => 'manufacturers_id', 'ref' => 'glpi_manufacturers'],
                 ['name' => 'B49D12667B3B43D', 'origin' => 'users_id', 'ref' => 'glpi_users'],
                 ['name' => 'B49D126F373DCF', 'origin' => 'groups_id', 'ref' => 'glpi_groups'],
                 ['name' => 'B49D126B17973F', 'origin' => 'states_id', 'ref' => 'glpi_states'],
             ],
             'glpi_phones' => [
                 ['name' => '61C3B8E4FD9C58DA', 'origin' => 'users_id_tech', 'ref' => 'glpi_users'],
                 ['name' => '61C3B8E41421F0A5', 'origin' => 'groups_id_tech', 'ref' => 'glpi_groups'],
                 ['name' => '61C3B8E4ED775E23', 'origin' => 'locations_id', 'ref' => 'glpi_locations'],
                 ['name' => '61C3B8E46AB85E18', 'origin' => 'phonetypes_id', 'ref' => 'glpi_phonetypes'],
                 ['name' => '61C3B8E43FE1E925', 'origin' => 'phonemodels_id', 'ref' => 'glpi_phonemodels'],
                 ['name' => '61C3B8E4EE911589', 'origin' => 'phonepowersupplies_id', 'ref' => 'glpi_phonepowersupplies'],
                 ['name' => '61C3B8E4A2A4C2E4', 'origin' => 'manufacturers_id', 'ref' => 'glpi_manufacturers'],
                 ['name' => '61C3B8E467B3B43D', 'origin' => 'users_id', 'ref' => 'glpi_users'],
                 ['name' => '61C3B8E4F373DCF', 'origin' => 'groups_id', 'ref' => 'glpi_groups'],
                 ['name' => '61C3B8E4B17973F', 'origin' => 'states_id', 'ref' => 'glpi_states'],
             ],
             'glpi_planningexternalevents' => [
                 ['name' => '544F3E8E67B3B43D', 'origin' => 'users_id', 'ref' => 'glpi_users'],
                 ['name' => '544F3E8ED5B73BE', 'origin' => 'users_id_guests', 'ref' => 'glpi_users'],
                 ['name' => '544F3E8EF373DCF', 'origin' => 'groups_id', 'ref' => 'glpi_groups'],
                 ['name' => '544F3E8E141A4D45', 'origin' => 'planningeventcategories_id', 'ref' => 'glpi_planningeventcategories'],
             ],
             'glpi_planningexternaleventtemplates' => [
                 ['name' => 'A85DD10C141A4D45', 'origin' => 'planningeventcategories_id', 'ref' => 'glpi_planningeventcategories'],
             ],
             'glpi_planningrecalls' => [
                 ['name' => '3BBA429167B3B43D', 'origin' => 'users_id', 'ref' => 'glpi_users'],
             ],
             'glpi_printers' => [
                 ['name' => '8F8D8A3DFD9C58DA', 'origin' => 'users_id_tech', 'ref' => 'glpi_users'],
                 ['name' => '8F8D8A3D1421F0A5', 'origin' => 'groups_id_tech', 'ref' => 'glpi_groups'],
                 ['name' => '8F8D8A3DED775E23', 'origin' => 'locations_id', 'ref' => 'glpi_locations'],
                 ['name' => '8F8D8A3D604D0C7C', 'origin' => 'networks_id', 'ref' => 'glpi_networks'],
                 ['name' => '8F8D8A3DDE7B282B', 'origin' => 'printertypes_id', 'ref' => 'glpi_printertypes'],
                 ['name' => '8F8D8A3D80854B45', 'origin' => 'printermodels_id', 'ref' => 'glpi_printermodels'],
                 ['name' => '8F8D8A3DA2A4C2E4', 'origin' => 'manufacturers_id', 'ref' => 'glpi_manufacturers'],
                 ['name' => '8F8D8A3D67B3B43D', 'origin' => 'users_id', 'ref' => 'glpi_users'],
                 ['name' => '8F8D8A3DF373DCF', 'origin' => 'groups_id', 'ref' => 'glpi_groups'],
                 ['name' => '8F8D8A3DB17973F', 'origin' => 'states_id', 'ref' => 'glpi_states'],
             ],
             'glpi_problemcosts' => [
                 ['name' => '5D343AA8E30A47DD', 'origin' => 'problems_id', 'ref' => 'glpi_problems'],
                 ['name' => '5D343AA822FD2D3D', 'origin' => 'budgets_id', 'ref' => 'glpi_budgets'],
             ],
             'glpi_problems' => [
                 ['name' => 'C4D3F5CFBB756162', 'origin' => 'users_id_recipient', 'ref' => 'glpi_users'],
                 ['name' => 'C4D3F5CF27D112BD', 'origin' => 'users_id_lastupdater', 'ref' => 'glpi_users'],
                 ['name' => 'C4D3F5CFEFE9C34D', 'origin' => 'itilcategories_id', 'ref' => 'glpi_itilcategories'],
             ],
             'glpi_problems_suppliers' => [
                 ['name' => '4A101DFE30A47DD', 'origin' => 'problems_id', 'ref' => 'glpi_problems'],
                 ['name' => '4A101DF355AF43', 'origin' => 'suppliers_id', 'ref' => 'glpi_suppliers'],
             ],
             'glpi_problems_tickets' => [
                 ['name' => '3DF11CF6E30A47DD', 'origin' => 'problems_id', 'ref' => 'glpi_problems'],
                 ['name' => '3DF11CF68FDC0E9A', 'origin' => 'tickets_id', 'ref' => 'glpi_tickets'],
             ],
             'glpi_problems_users' => [
                 ['name' => '5C9612D2E30A47DD', 'origin' => 'problems_id', 'ref' => 'glpi_problems'],
                 ['name' => '5C9612D267B3B43D', 'origin' => 'users_id', 'ref' => 'glpi_users'],
             ],
             'glpi_problemtasks' => [
                 ['name' => 'A2710897E30A47DD', 'origin' => 'problems_id', 'ref' => 'glpi_problems'],
                 ['name' => 'A271089730D85233', 'origin' => 'taskcategories_id', 'ref' => 'glpi_taskcategories'],
                 ['name' => 'A271089767B3B43D', 'origin' => 'users_id', 'ref' => 'glpi_users'],
                 ['name' => 'A27108978CBB3EB6', 'origin' => 'users_id_editor', 'ref' => 'glpi_users'],
                 ['name' => 'A2710897FD9C58DA', 'origin' => 'users_id_tech', 'ref' => 'glpi_users'],
                 ['name' => 'A27108971421F0A5', 'origin' => 'groups_id_tech', 'ref' => 'glpi_groups'],
                 ['name' => 'A27108973A064358', 'origin' => 'tasktemplates_id', 'ref' => 'glpi_tasktemplates'],
             ],
             'glpi_problemtemplatehiddenfields' => [
                 ['name' => 'D90AB3B47A8D7635', 'origin' => 'problemtemplates_id', 'ref' => 'glpi_problemtemplates'],
             ],
             'glpi_problemtemplatemandatoryfields' => [
                 ['name' => '20DA01337A8D7635', 'origin' => 'problemtemplates_id', 'ref' => 'glpi_problemtemplates'],
             ],
             'glpi_problemtemplatepredefinedfields' => [
                 ['name' => '1D77B16A7A8D7635', 'origin' => 'problemtemplates_id', 'ref' => 'glpi_problemtemplates'],
             ],
             'glpi_profilerights' => [
                 ['name' => '6E4E481722077C89', 'origin' => 'profiles_id', 'ref' => 'glpi_profiles'],
             ],
             'glpi_profiles' => [
                 ['name' => 'C18512BA10E3E815', 'origin' => 'tickettemplates_id', 'ref' => 'glpi_tickettemplates'],
                 ['name' => 'C18512BA64105530', 'origin' => 'changetemplates_id', 'ref' => 'glpi_changetemplates'],
                 ['name' => 'C18512BA7A8D7635', 'origin' => 'problemtemplates_id', 'ref' => 'glpi_problemtemplates'],
             ],
             'glpi_profiles_reminders' => [
                 ['name' => '4A5D764FC7C7BF28', 'origin' => 'reminders_id', 'ref' => 'glpi_reminders'],
                 ['name' => '4A5D764F22077C89', 'origin' => 'profiles_id', 'ref' => 'glpi_profiles'],
             ],
             'glpi_profiles_rssfeeds' => [
                 ['name' => '8AE4CF1E2920D1F', 'origin' => 'rssfeeds_id', 'ref' => 'glpi_rssfeeds'],
                 ['name' => '8AE4CF1E22077C89', 'origin' => 'profiles_id', 'ref' => 'glpi_profiles'],
             ],
             'glpi_profiles_users' => [
                 ['name' => '752007FA67B3B43D', 'origin' => 'users_id', 'ref' => 'glpi_users'],
                 ['name' => '752007FA22077C89', 'origin' => 'profiles_id', 'ref' => 'glpi_profiles'],
             ],
             'glpi_projectcosts' => [
                 ['name' => 'BEAAE5F21EDE0F55', 'origin' => 'projects_id', 'ref' => 'glpi_projects'],
                 ['name' => 'BEAAE5F222FD2D3D', 'origin' => 'budgets_id', 'ref' => 'glpi_budgets'],
             ],
             'glpi_projects' => [
                 ['name' => '1626242E1EDE0F55', 'origin' => 'projects_id', 'ref' => 'glpi_projects'],
                 ['name' => '1626242E18995984', 'origin' => 'projectstates_id', 'ref' => 'glpi_projectstates'],
                 ['name' => '1626242E6CE4DE4F', 'origin' => 'projecttypes_id', 'ref' => 'glpi_projecttypes'],
                 ['name' => '1626242E67B3B43D', 'origin' => 'users_id', 'ref' => 'glpi_users'],
                 ['name' => '1626242EF373DCF', 'origin' => 'groups_id', 'ref' => 'glpi_groups'],
             ],
             'glpi_projecttasks' => [
                 ['name' => '41EFD7CD1EDE0F55', 'origin' => 'projects_id', 'ref' => 'glpi_projects'],
                 ['name' => '41EFD7CD171C029', 'origin' => 'projecttasks_id', 'ref' => 'glpi_projecttasks'],
                 ['name' => '41EFD7CD18995984', 'origin' => 'projectstates_id', 'ref' => 'glpi_projectstates'],
                 ['name' => '41EFD7CD7369BDC5', 'origin' => 'projecttasktypes_id', 'ref' => 'glpi_projecttasktypes'],
                 ['name' => '41EFD7CD67B3B43D', 'origin' => 'users_id', 'ref' => 'glpi_users'],
                 ['name' => '41EFD7CD7FECD144', 'origin' => 'projecttasktemplates_id', 'ref' => 'glpi_projecttasktemplates'],
             ],
             'glpi_projecttasks_tickets' => [
                 ['name' => '2D48CB0A8FDC0E9A', 'origin' => 'tickets_id', 'ref' => 'glpi_tickets'],
                 ['name' => '2D48CB0A171C029', 'origin' => 'projecttasks_id', 'ref' => 'glpi_projecttasks'],
             ],
             'glpi_projecttaskteams' => [
                 ['name' => '1B0A1B0D171C029', 'origin' => 'projecttasks_id', 'ref' => 'glpi_projecttasks'],
             ],
             'glpi_projecttasktemplates' => [
                 ['name' => '286BFEDA1EDE0F55', 'origin' => 'projects_id', 'ref' => 'glpi_projects'],
                 ['name' => '286BFEDA171C029', 'origin' => 'projecttasks_id', 'ref' => 'glpi_projecttasks'],
                 ['name' => '286BFEDA18995984', 'origin' => 'projectstates_id', 'ref' => 'glpi_projectstates'],
                 ['name' => '286BFEDA7369BDC5', 'origin' => 'projecttasktypes_id', 'ref' => 'glpi_projecttasktypes'],
                 ['name' => '286BFEDA67B3B43D', 'origin' => 'users_id', 'ref' => 'glpi_users'],
             ],
             'glpi_projectteams' => [
                 ['name' => '877590021EDE0F55', 'origin' => 'projects_id', 'ref' => 'glpi_projects'],
             ],
             'glpi_queuedchats' => [
                 ['name' => '7E072DC2A9E8DD2B', 'origin' => 'notificationtemplates_id', 'ref' => 'glpi_notificationtemplates'],
                 ['name' => '7E072DC2ED775E23', 'origin' => 'locations_id', 'ref' => 'glpi_locations'],
                 ['name' => '7E072DC2F373DCF', 'origin' => 'groups_id', 'ref' => 'glpi_groups'],
                 ['name' => '7E072DC2EFE9C34D', 'origin' => 'itilcategories_id', 'ref' => 'glpi_itilcategories'],
             ],
             'glpi_queuednotifications' => [
                 ['name' => 'FDE96054A9E8DD2B', 'origin' => 'notificationtemplates_id', 'ref' => 'glpi_notificationtemplates'],
             ],
             'glpi_racks' => [
                 ['name' => '205CE311ED775E23', 'origin' => 'locations_id', 'ref' => 'glpi_locations'],
                 ['name' => '205CE311B9750B73', 'origin' => 'rackmodels_id', 'ref' => 'glpi_rackmodels'],
                 ['name' => '205CE311A2A4C2E4', 'origin' => 'manufacturers_id', 'ref' => 'glpi_manufacturers'],
                 ['name' => '205CE3112D88DCC8', 'origin' => 'racktypes_id', 'ref' => 'glpi_racktypes'],
                 ['name' => '205CE311B17973F', 'origin' => 'states_id', 'ref' => 'glpi_states'],
                 ['name' => '205CE311FD9C58DA', 'origin' => 'users_id_tech', 'ref' => 'glpi_users'],
                 ['name' => '205CE3111421F0A5', 'origin' => 'groups_id_tech', 'ref' => 'glpi_groups'],
                 ['name' => '205CE311B569C6DF', 'origin' => 'dcrooms_id', 'ref' => 'glpi_dcrooms'],
             ],
             'glpi_reminders' => [
                 ['name' => '60B5667D67B3B43D', 'origin' => 'users_id', 'ref' => 'glpi_users'],
             ],
             'glpi_reminders_users' => [
                 ['name' => '5D0EA00FC7C7BF28', 'origin' => 'reminders_id', 'ref' => 'glpi_reminders'],
                 ['name' => '5D0EA00F67B3B43D', 'origin' => 'users_id', 'ref' => 'glpi_users'],
             ],
             'glpi_remindertranslations' => [
                 ['name' => 'BE66B0AAC7C7BF28', 'origin' => 'reminders_id', 'ref' => 'glpi_reminders'],
                 ['name' => 'BE66B0AA67B3B43D', 'origin' => 'users_id', 'ref' => 'glpi_users'],
             ],
             'glpi_reservations' => [
                 ['name' => '754EA860786DF47C', 'origin' => 'reservationitems_id', 'ref' => 'glpi_reservationitems'],
                 ['name' => '754EA86067B3B43D', 'origin' => 'users_id', 'ref' => 'glpi_users'],
             ],
             'glpi_rssfeeds' => [
                 ['name' => 'DDF69E567B3B43D', 'origin' => 'users_id', 'ref' => 'glpi_users'],
             ],
             'glpi_rssfeeds_users' => [
                 ['name' => '3AFFECE42920D1F', 'origin' => 'rssfeeds_id', 'ref' => 'glpi_rssfeeds'],
                 ['name' => '3AFFECE467B3B43D', 'origin' => 'users_id', 'ref' => 'glpi_users'],
             ],
             'glpi_ruleactions' => [
                 ['name' => 'E78233EFB699244', 'origin' => 'rules_id', 'ref' => 'glpi_rules'],
             ],
             'glpi_rulecriterias' => [
                 ['name' => '71F92FB0FB699244', 'origin' => 'rules_id', 'ref' => 'glpi_rules'],
             ],
             'glpi_savedsearches' => [
                 ['name' => '8C93FCA967B3B43D', 'origin' => 'users_id', 'ref' => 'glpi_users'],
             ],
             'glpi_savedsearches_alerts' => [
                 ['name' => '8F033C74D137DC92', 'origin' => 'savedsearches_id', 'ref' => 'glpi_savedsearches'],
             ],
             'glpi_savedsearches_users' => [
                 ['name' => '6AB618A167B3B43D', 'origin' => 'users_id', 'ref' => 'glpi_users'],
                 ['name' => '6AB618A1D137DC92', 'origin' => 'savedsearches_id', 'ref' => 'glpi_savedsearches'],
             ],
             'glpi_slalevelactions' => [
                 ['name' => '4B2CB33557FD051', 'origin' => 'slalevels_id', 'ref' => 'glpi_slalevels'],
             ],
             'glpi_slalevelcriterias' => [
                 ['name' => '6202206B57FD051', 'origin' => 'slalevels_id', 'ref' => 'glpi_slalevels'],
             ],
             'glpi_slalevels' => [
                 ['name' => 'A66D03087B029744', 'origin' => 'slas_id', 'ref' => 'glpi_slas'],
             ],
             'glpi_slalevels_tickets' => [
                 ['name' => '890E0B138FDC0E9A', 'origin' => 'tickets_id', 'ref' => 'glpi_tickets'],
                 ['name' => '890E0B1357FD051', 'origin' => 'slalevels_id', 'ref' => 'glpi_slalevels'],
             ],
             'glpi_slas' => [
                 ['name' => 'AD32DCC272C4B705', 'origin' => 'calendars_id', 'ref' => 'glpi_calendars'],
                 ['name' => 'AD32DCC2BEF27A45', 'origin' => 'slms_id', 'ref' => 'glpi_slms'],
             ],
             'glpi_slms' => [
                 ['name' => '18793CE72C4B705', 'origin' => 'calendars_id', 'ref' => 'glpi_calendars'],
             ],
             'glpi_softwarecategories' => [
                 ['name' => '5A90EC8AAD111992', 'origin' => 'softwarecategories_id', 'ref' => 'glpi_softwarecategories'],
             ],
             'glpi_softwarelicenses' => [
                 ['name' => '8DF16B58E67D8904', 'origin' => 'softwares_id', 'ref' => 'glpi_softwares'],
                 ['name' => '8DF16B5844CA6F2F', 'origin' => 'softwarelicenses_id', 'ref' => 'glpi_softwarelicenses'],
                 ['name' => '8DF16B5885A13A28', 'origin' => 'softwarelicensetypes_id', 'ref' => 'glpi_softwarelicensetypes'],
                 ['name' => '8DF16B586C46BCBA', 'origin' => 'softwareversions_id_buy', 'ref' => 'glpi_softwareversions'],
                 ['name' => '8DF16B583774F286', 'origin' => 'softwareversions_id_use', 'ref' => 'glpi_softwareversions'],
                 ['name' => '8DF16B58ED775E23', 'origin' => 'locations_id', 'ref' => 'glpi_locations'],
                 ['name' => '8DF16B58FD9C58DA', 'origin' => 'users_id_tech', 'ref' => 'glpi_users'],
                 ['name' => '8DF16B5867B3B43D', 'origin' => 'users_id', 'ref' => 'glpi_users'],
                 ['name' => '8DF16B581421F0A5', 'origin' => 'groups_id_tech', 'ref' => 'glpi_groups'],
                 ['name' => '8DF16B58B17973F', 'origin' => 'states_id', 'ref' => 'glpi_states'],
                 ['name' => '8DF16B58A2A4C2E4', 'origin' => 'manufacturers_id', 'ref' => 'glpi_manufacturers'],
             ],
             'glpi_softwarelicensetypes' => [
                 ['name' => 'D4B117C385A13A28', 'origin' => 'softwarelicensetypes_id', 'ref' => 'glpi_softwarelicensetypes'],
             ],
             'glpi_softwares' => [
                 ['name' => '1D851FEBED775E23', 'origin' => 'locations_id', 'ref' => 'glpi_locations'],
                 ['name' => '1D851FEBFD9C58DA', 'origin' => 'users_id_tech', 'ref' => 'glpi_users'],
                 ['name' => '1D851FEB1421F0A5', 'origin' => 'groups_id_tech', 'ref' => 'glpi_groups'],
                 ['name' => '1D851FEBE67D8904', 'origin' => 'softwares_id', 'ref' => 'glpi_softwares'],
                 ['name' => '1D851FEBA2A4C2E4', 'origin' => 'manufacturers_id', 'ref' => 'glpi_manufacturers'],
                 ['name' => '1D851FEB67B3B43D', 'origin' => 'users_id', 'ref' => 'glpi_users'],
                 ['name' => '1D851FEBF373DCF', 'origin' => 'groups_id', 'ref' => 'glpi_groups'],
             ],
             'glpi_softwareversions' => [
                 ['name' => 'EB1F24B5E67D8904', 'origin' => 'softwares_id', 'ref' => 'glpi_softwares'],
                 ['name' => 'EB1F24B57F852578', 'origin' => 'operatingsystems_id', 'ref' => 'glpi_operatingsystems'],
             ],
             'glpi_solutiontemplates' => [
                 ['name' => '6048BE7A5E58E090', 'origin' => 'solutiontypes_id', 'ref' => 'glpi_solutiontypes'],
             ],
             'glpi_states' => [
                 ['name' => 'B329E15CB17973F', 'origin' => 'states_id', 'ref' => 'glpi_states'],
             ],
             'glpi_suppliers' => [
                 ['name' => 'A10F66F57B9FA635', 'origin' => 'suppliertypes_id', 'ref' => 'glpi_suppliertypes'],
             ],
             'glpi_suppliers_tickets' => [
                 ['name' => 'C3F21B8B8FDC0E9A', 'origin' => 'tickets_id', 'ref' => 'glpi_tickets'],
                 ['name' => 'C3F21B8B355AF43', 'origin' => 'suppliers_id', 'ref' => 'glpi_suppliers'],
             ],
             'glpi_taskcategories' => [
                 ['name' => '83E9502430D85233', 'origin' => 'taskcategories_id', 'ref' => 'glpi_taskcategories'],
                 ['name' => '83E95024551BC90F', 'origin' => 'knowbaseitemcategories_id', 'ref' => 'glpi_knowbaseitemcategories'],
             ],
             'glpi_tasktemplates' => [
                 ['name' => '13EA38F830D85233', 'origin' => 'taskcategories_id', 'ref' => 'glpi_taskcategories'],
                 ['name' => '13EA38F8FD9C58DA', 'origin' => 'users_id_tech', 'ref' => 'glpi_users'],
                 ['name' => '13EA38F81421F0A5', 'origin' => 'groups_id_tech', 'ref' => 'glpi_groups'],
             ],
             'glpi_ticketcosts' => [
                 ['name' => 'A94AF7498FDC0E9A', 'origin' => 'tickets_id', 'ref' => 'glpi_tickets'],
                 ['name' => 'A94AF74922FD2D3D', 'origin' => 'budgets_id', 'ref' => 'glpi_budgets'],
             ],
         ];

        // Désactiver les vérifications des clés étrangères
        $this->addSql('SET foreign_key_checks = 0;');

        foreach ($transformFkTables as $table => $rows) {
            foreach ($rows as $row) {

                $tableName = $table;
                $foreignKey = $row['origin'];
                $name = $row['name'];
                $ref = $row['ref'];
                $this->addSql("ALTER TABLE `{$tableName}` MODIFY COLUMN `{$foreignKey}` INT NULL");
                $this->addSql("UPDATE `{$tableName}` SET `{$foreignKey}` = NULL WHERE `{$foreignKey}` = 0");
                $this->addSql("ALTER TABLE `{$tableName}` ADD CONSTRAINT `{$name}` FOREIGN KEY (`{$foreignKey}`) REFERENCES `{$ref}` (`id`)");
            }
        }
        $this->addSql('ALTER TABLE glpi_apiclients CHANGE entities_id entities_id INT DEFAULT 0');
        $this->addSql('ALTER TABLE glpi_apiclients ADD CONSTRAINT FK_D00BB2E46145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');
        $this->addSql('CREATE INDEX IDX_D00BB2E46145D7DB ON glpi_apiclients (entities_id)');
        $this->addSql('ALTER TABLE glpi_appliances CHANGE entities_id entities_id INT DEFAULT 0, CHANGE locations_id locations_id INT DEFAULT NULL, CHANGE manufacturers_id manufacturers_id INT DEFAULT NULL, CHANGE applianceenvironments_id applianceenvironments_id INT DEFAULT NULL, CHANGE users_id users_id INT DEFAULT NULL, CHANGE users_id_tech users_id_tech INT DEFAULT NULL, CHANGE groups_id groups_id INT DEFAULT NULL, CHANGE groups_id_tech groups_id_tech INT DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_appliances ADD CONSTRAINT FK_A90A053D6145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');
        $this->addSql('ALTER TABLE glpi_appliances_items CHANGE appliances_id appliances_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_appliancetypes CHANGE entities_id entities_id INT DEFAULT 0');
        $this->addSql('ALTER TABLE glpi_appliancetypes ADD CONSTRAINT FK_514B2A7F6145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');
        $this->addSql('ALTER TABLE glpi_authldapreplicates CHANGE authldaps_id authldaps_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_budgets CHANGE entities_id entities_id INT DEFAULT 0, CHANGE value value NUMERIC(20, 4) DEFAULT \'0\' NOT NULL, CHANGE locations_id locations_id INT DEFAULT NULL, CHANGE budgettypes_id budgettypes_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_budgets ADD CONSTRAINT FK_B6985E2C6145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');
        $this->addSql('ALTER TABLE glpi_businesscriticities CHANGE entities_id entities_id INT DEFAULT 0, CHANGE businesscriticities_id businesscriticities_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_businesscriticities ADD CONSTRAINT FK_5119F8B46145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');
        $this->addSql('CREATE INDEX IDX_5119F8B46145D7DB ON glpi_businesscriticities (entities_id)');
        $this->addSql('CREATE INDEX IDX_5119F8B4FCE88FAB ON glpi_businesscriticities (businesscriticities_id)');
        $this->addSql('ALTER TABLE glpi_calendars CHANGE entities_id entities_id INT DEFAULT 0');
        $this->addSql('ALTER TABLE glpi_calendars ADD CONSTRAINT FK_89F85DA66145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');
        $this->addSql('ALTER TABLE glpi_calendars_holidays CHANGE calendars_id calendars_id INT DEFAULT NULL, CHANGE holidays_id holidays_id INT DEFAULT NULL');
        $this->addSql('CREATE INDEX IDX_2315C8B372C4B705 ON glpi_calendars_holidays (calendars_id)');
        $this->addSql('ALTER TABLE glpi_calendarsegments CHANGE calendars_id calendars_id INT DEFAULT NULL, CHANGE entities_id entities_id INT DEFAULT 0');
        $this->addSql('ALTER TABLE glpi_calendarsegments ADD CONSTRAINT FK_8021521D6145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');
        $this->addSql('CREATE INDEX IDX_8021521D6145D7DB ON glpi_calendarsegments (entities_id)');
        $this->addSql('ALTER TABLE glpi_cartridgeitems CHANGE entities_id entities_id INT DEFAULT 0, CHANGE locations_id locations_id INT DEFAULT NULL, CHANGE cartridgeitemtypes_id cartridgeitemtypes_id INT DEFAULT NULL, CHANGE manufacturers_id manufacturers_id INT DEFAULT NULL, CHANGE users_id_tech users_id_tech INT DEFAULT NULL, CHANGE groups_id_tech groups_id_tech INT DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_cartridgeitems ADD CONSTRAINT FK_988DAA3D6145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');
        $this->addSql('ALTER TABLE glpi_cartridgeitems_printermodels CHANGE cartridgeitems_id cartridgeitems_id INT DEFAULT NULL, CHANGE printermodels_id printermodels_id INT DEFAULT NULL');
        $this->addSql('CREATE INDEX IDX_856AD7A780854B45 ON glpi_cartridgeitems_printermodels (printermodels_id)');
        $this->addSql('ALTER TABLE glpi_cartridges CHANGE entities_id entities_id INT DEFAULT 0, CHANGE cartridgeitems_id cartridgeitems_id INT DEFAULT NULL, CHANGE printers_id printers_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_cartridges ADD CONSTRAINT FK_3185A7C76145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');
        $this->addSql('ALTER TABLE glpi_certificates CHANGE entities_id entities_id INT DEFAULT 0, CHANGE certificatetypes_id certificatetypes_id INT DEFAULT NULL, CHANGE users_id_tech users_id_tech INT DEFAULT NULL, CHANGE groups_id_tech groups_id_tech INT DEFAULT NULL COMMENT \'RELATION to glpi_groups (id)\', CHANGE locations_id locations_id INT DEFAULT NULL COMMENT \'RELATION to glpi_locations (id)\', CHANGE manufacturers_id manufacturers_id INT DEFAULT NULL COMMENT \'RELATION to glpi_manufacturers (id)\', CHANGE users_id users_id INT DEFAULT NULL, CHANGE groups_id groups_id INT DEFAULT NULL, CHANGE states_id states_id INT DEFAULT NULL COMMENT \'RELATION to states (id)\'');
        $this->addSql('ALTER TABLE glpi_certificates ADD CONSTRAINT FK_F825F1066145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');
        $this->addSql('ALTER TABLE glpi_certificates_items CHANGE certificates_id certificates_id INT DEFAULT NULL');
        $this->addSql('CREATE INDEX IDX_E410E24524E411BB ON glpi_certificates_items (certificates_id)');
        $this->addSql('ALTER TABLE glpi_certificatetypes CHANGE entities_id entities_id INT DEFAULT 0');
        $this->addSql('ALTER TABLE glpi_certificatetypes ADD CONSTRAINT FK_CADCD7DC6145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');
        $this->addSql('ALTER TABLE glpi_changecosts CHANGE changes_id changes_id INT DEFAULT NULL, CHANGE cost_time cost_time NUMERIC(20, 4) DEFAULT \'0\' NOT NULL, CHANGE cost_fixed cost_fixed NUMERIC(20, 4) DEFAULT \'0\' NOT NULL, CHANGE cost_material cost_material NUMERIC(20, 4) DEFAULT \'0\' NOT NULL, CHANGE budgets_id budgets_id INT DEFAULT NULL, CHANGE entities_id entities_id INT DEFAULT 0');
        $this->addSql('ALTER TABLE glpi_changecosts ADD CONSTRAINT FK_F846ABCA6145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');
        $this->addSql('ALTER TABLE glpi_changes CHANGE entities_id entities_id INT DEFAULT 0, CHANGE users_id_recipient users_id_recipient INT DEFAULT NULL, CHANGE users_id_lastupdater users_id_lastupdater INT DEFAULT NULL, CHANGE itilcategories_id itilcategories_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_changes ADD CONSTRAINT FK_4A1273596145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');
        $this->addSql('ALTER TABLE glpi_changes_groups CHANGE changes_id changes_id INT DEFAULT NULL, CHANGE groups_id groups_id INT DEFAULT NULL');
        $this->addSql('CREATE INDEX IDX_DC2C71435D80B7AB ON glpi_changes_groups (changes_id)');
        $this->addSql('CREATE INDEX IDX_DC2C7143F373DCF ON glpi_changes_groups (groups_id)');
        $this->addSql('ALTER TABLE glpi_changes_items CHANGE changes_id changes_id INT DEFAULT NULL');
        $this->addSql('CREATE INDEX IDX_79C851F35D80B7AB ON glpi_changes_items (changes_id)');
        $this->addSql('ALTER TABLE glpi_changes_problems CHANGE changes_id changes_id INT DEFAULT NULL, CHANGE problems_id problems_id INT DEFAULT NULL');
        $this->addSql('CREATE INDEX IDX_20AE2965D80B7AB ON glpi_changes_problems (changes_id)');
        $this->addSql('ALTER TABLE glpi_changes_suppliers CHANGE changes_id changes_id INT DEFAULT NULL, CHANGE suppliers_id suppliers_id INT DEFAULT NULL');
        $this->addSql('CREATE INDEX IDX_B37E56B25D80B7AB ON glpi_changes_suppliers (changes_id)');
        $this->addSql('CREATE INDEX IDX_B37E56B2355AF43 ON glpi_changes_suppliers (suppliers_id)');
        $this->addSql('ALTER TABLE glpi_changes_tickets CHANGE changes_id changes_id INT DEFAULT NULL, CHANGE tickets_id tickets_id INT DEFAULT NULL');
        $this->addSql('CREATE INDEX IDX_EBBABDAA5D80B7AB ON glpi_changes_tickets (changes_id)');
        $this->addSql('ALTER TABLE glpi_changes_users CHANGE changes_id changes_id INT DEFAULT NULL, CHANGE users_id users_id INT DEFAULT NULL');
        $this->addSql('CREATE INDEX IDX_8C551D575D80B7AB ON glpi_changes_users (changes_id)');
        $this->addSql('CREATE INDEX IDX_8C551D5767B3B43D ON glpi_changes_users (users_id)');
        $this->addSql('ALTER TABLE glpi_changetasks CHANGE changes_id changes_id INT DEFAULT NULL, CHANGE taskcategories_id taskcategories_id INT DEFAULT NULL, CHANGE users_id users_id INT DEFAULT NULL, CHANGE users_id_editor users_id_editor INT DEFAULT NULL, CHANGE users_id_tech users_id_tech INT DEFAULT NULL, CHANGE groups_id_tech groups_id_tech INT DEFAULT NULL, CHANGE tasktemplates_id tasktemplates_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_changetemplatehiddenfields CHANGE changetemplates_id changetemplates_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_changetemplatemandatoryfields CHANGE changetemplates_id changetemplates_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_changetemplatepredefinedfields CHANGE changetemplates_id changetemplates_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_changetemplates CHANGE entities_id entities_id INT DEFAULT 0');
        $this->addSql('ALTER TABLE glpi_changetemplates ADD CONSTRAINT FK_EE99887A6145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');
        $this->addSql('ALTER TABLE glpi_changevalidations CHANGE entities_id entities_id INT DEFAULT 0, CHANGE users_id users_id INT DEFAULT NULL, CHANGE changes_id changes_id INT DEFAULT NULL, CHANGE users_id_validate users_id_validate INT DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_changevalidations ADD CONSTRAINT FK_C158C946145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');
        $this->addSql('ALTER TABLE glpi_clusters CHANGE entities_id entities_id INT DEFAULT 0, CHANGE users_id_tech users_id_tech INT DEFAULT NULL, CHANGE groups_id_tech groups_id_tech INT DEFAULT NULL, CHANGE states_id states_id INT DEFAULT NULL COMMENT \'RELATION to states (id)\', CHANGE clustertypes_id clustertypes_id INT DEFAULT NULL, CHANGE autoupdatesystems_id autoupdatesystems_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_clusters ADD CONSTRAINT FK_A63CCAB56145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');
        $this->addSql('ALTER TABLE glpi_clustertypes CHANGE entities_id entities_id INT DEFAULT 0');
        $this->addSql('ALTER TABLE glpi_clustertypes ADD CONSTRAINT FK_FAF6E9326145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');
        $this->addSql('ALTER TABLE glpi_computerantiviruses CHANGE computers_id computers_id INT DEFAULT NULL, CHANGE manufacturers_id manufacturers_id INT DEFAULT NULL');
        $this->addSql('CREATE INDEX IDX_68671079A2A4C2E4 ON glpi_computerantiviruses (manufacturers_id)');
        $this->addSql('ALTER TABLE glpi_computers CHANGE entities_id entities_id INT DEFAULT 0, CHANGE users_id_tech users_id_tech INT DEFAULT NULL, CHANGE groups_id_tech groups_id_tech INT DEFAULT NULL, CHANGE locations_id locations_id INT DEFAULT NULL, CHANGE networks_id networks_id INT DEFAULT NULL, CHANGE computermodels_id computermodels_id INT DEFAULT NULL, CHANGE computertypes_id computertypes_id INT DEFAULT NULL, CHANGE manufacturers_id manufacturers_id INT DEFAULT NULL, CHANGE users_id users_id INT DEFAULT NULL, CHANGE groups_id groups_id INT DEFAULT NULL, CHANGE states_id states_id INT DEFAULT NULL, CHANGE ticket_tco ticket_tco NUMERIC(20, 4) DEFAULT \'0\'');
        $this->addSql('ALTER TABLE glpi_computers ADD CONSTRAINT FK_293E8ED86145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');
        $this->addSql('ALTER TABLE glpi_computers_items CHANGE computers_id computers_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_computervirtualmachines CHANGE entities_id entities_id INT DEFAULT 0, CHANGE computers_id computers_id INT DEFAULT NULL, CHANGE virtualmachinestates_id virtualmachinestates_id INT DEFAULT NULL, CHANGE virtualmachinesystems_id virtualmachinesystems_id INT DEFAULT NULL, CHANGE virtualmachinetypes_id virtualmachinetypes_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_computervirtualmachines ADD CONSTRAINT FK_6FDC320C6145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');
        $this->addSql('CREATE INDEX IDX_6FDC320C10B10554 ON glpi_computervirtualmachines (virtualmachinetypes_id)');
        $this->addSql('ALTER TABLE glpi_consumableitems CHANGE entities_id entities_id INT DEFAULT 0, CHANGE locations_id locations_id INT DEFAULT NULL, CHANGE consumableitemtypes_id consumableitemtypes_id INT DEFAULT NULL, CHANGE manufacturers_id manufacturers_id INT DEFAULT NULL, CHANGE users_id_tech users_id_tech INT DEFAULT NULL, CHANGE groups_id_tech groups_id_tech INT DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_consumableitems ADD CONSTRAINT FK_B83ADB4A6145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');
        $this->addSql('ALTER TABLE glpi_consumables CHANGE entities_id entities_id INT DEFAULT 0, CHANGE consumableitems_id consumableitems_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_consumables ADD CONSTRAINT FK_F28618F26145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');
        $this->addSql('ALTER TABLE glpi_contacts CHANGE entities_id entities_id INT DEFAULT 0, CHANGE contacttypes_id contacttypes_id INT DEFAULT NULL, CHANGE usertitles_id usertitles_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_contacts ADD CONSTRAINT FK_79F582F96145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');
        $this->addSql('ALTER TABLE glpi_contacts_suppliers CHANGE suppliers_id suppliers_id INT DEFAULT NULL, CHANGE contacts_id contacts_id INT DEFAULT NULL');
        $this->addSql('CREATE INDEX IDX_8B35180D355AF43 ON glpi_contacts_suppliers (suppliers_id)');
        $this->addSql('ALTER TABLE glpi_contractcosts CHANGE contracts_id contracts_id INT DEFAULT NULL, CHANGE budgets_id budgets_id INT DEFAULT NULL, CHANGE entities_id entities_id INT DEFAULT 0');
        $this->addSql('ALTER TABLE glpi_contractcosts ADD CONSTRAINT FK_888F83816145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');
        $this->addSql('ALTER TABLE glpi_contracts CHANGE entities_id entities_id INT DEFAULT 0, CHANGE contracttypes_id contracttypes_id INT DEFAULT NULL, CHANGE states_id states_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_contracts ADD CONSTRAINT FK_47776DA6145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');


        $this->addSql('ALTER TABLE glpi_contracts_items CHANGE contracts_id contracts_id INT DEFAULT NULL');

        $this->addSql('CREATE INDEX IDX_5FF01F0E24584564 ON glpi_contracts_items (contracts_id)');
        $this->addSql('ALTER TABLE glpi_contracts_suppliers CHANGE suppliers_id suppliers_id INT DEFAULT NULL, CHANGE contracts_id contracts_id INT DEFAULT NULL');


        $this->addSql('CREATE INDEX IDX_78E40104355AF43 ON glpi_contracts_suppliers (suppliers_id)');
        $this->addSql('ALTER TABLE glpi_crontasklogs CHANGE crontasks_id crontasks_id INT DEFAULT NULL');


        $this->addSql('CREATE INDEX IDX_F45D647F2D2CC539 ON glpi_crontasklogs (crontasklogs_id)');
        $this->addSql('ALTER TABLE glpi_dashboards CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE profileId profileId INT DEFAULT NULL, CHANGE userId userId INT DEFAULT NULL');


        $this->addSql('CREATE INDEX IDX_7331D499B26949C ON glpi_dashboards (profileId)');
        $this->addSql('CREATE INDEX IDX_7331D4964B64DCC ON glpi_dashboards (userId)');
        $this->addSql('ALTER TABLE glpi_datacenters CHANGE entities_id entities_id INT DEFAULT 0, CHANGE locations_id locations_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_datacenters ADD CONSTRAINT FK_D729C8696145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');

        $this->addSql('ALTER TABLE glpi_dcrooms CHANGE entities_id entities_id INT DEFAULT 0, CHANGE locations_id locations_id INT DEFAULT NULL, CHANGE datacenters_id datacenters_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_dcrooms ADD CONSTRAINT FK_BC44EC936145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');


        $this->addSql('ALTER TABLE glpi_devicebatteries CHANGE manufacturers_id manufacturers_id INT DEFAULT NULL, CHANGE devicebatterytypes_id devicebatterytypes_id INT DEFAULT NULL, CHANGE entities_id entities_id INT DEFAULT 0');


        $this->addSql('ALTER TABLE glpi_devicebatteries ADD CONSTRAINT FK_A652C99D6145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');

        $this->addSql('ALTER TABLE glpi_devicecases CHANGE devicecasetypes_id devicecasetypes_id INT DEFAULT NULL, CHANGE manufacturers_id manufacturers_id INT DEFAULT NULL, CHANGE entities_id entities_id INT DEFAULT 0');


        $this->addSql('ALTER TABLE glpi_devicecases ADD CONSTRAINT FK_A1AE63686145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');

        $this->addSql('ALTER TABLE glpi_devicecontrols CHANGE manufacturers_id manufacturers_id INT DEFAULT NULL, CHANGE interfacetypes_id interfacetypes_id INT DEFAULT NULL, CHANGE entities_id entities_id INT DEFAULT 0');


        $this->addSql('ALTER TABLE glpi_devicecontrols ADD CONSTRAINT FK_8FBFCEDF6145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');

        $this->addSql('ALTER TABLE glpi_devicedrives CHANGE manufacturers_id manufacturers_id INT DEFAULT NULL, CHANGE interfacetypes_id interfacetypes_id INT DEFAULT NULL');



        $this->addSql('ALTER TABLE glpi_devicefirmwares CHANGE manufacturers_id manufacturers_id INT DEFAULT NULL, CHANGE devicefirmwaretypes_id devicefirmwaretypes_id INT DEFAULT NULL, CHANGE entities_id entities_id INT DEFAULT 0');


        $this->addSql('ALTER TABLE glpi_devicefirmwares ADD CONSTRAINT FK_27AD954F6145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');

        $this->addSql('ALTER TABLE glpi_devicegenerics CHANGE devicegenerictypes_id devicegenerictypes_id INT DEFAULT NULL, CHANGE manufacturers_id manufacturers_id INT DEFAULT NULL, CHANGE entities_id entities_id INT DEFAULT 0, CHANGE locations_id locations_id INT DEFAULT NULL, CHANGE states_id states_id INT DEFAULT NULL');


        $this->addSql('ALTER TABLE glpi_devicegenerics ADD CONSTRAINT FK_711041246145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');



        $this->addSql('ALTER TABLE glpi_devicegraphiccards CHANGE interfacetypes_id interfacetypes_id INT DEFAULT NULL, CHANGE manufacturers_id manufacturers_id INT DEFAULT NULL, CHANGE entities_id entities_id INT DEFAULT 0');


        $this->addSql('ALTER TABLE glpi_devicegraphiccards ADD CONSTRAINT FK_13F4C69E6145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');

        $this->addSql('ALTER TABLE glpi_deviceharddrives CHANGE interfacetypes_id interfacetypes_id INT DEFAULT NULL, CHANGE manufacturers_id manufacturers_id INT DEFAULT NULL, CHANGE entities_id entities_id INT DEFAULT 0');


        $this->addSql('ALTER TABLE glpi_deviceharddrives ADD CONSTRAINT FK_EA210CE06145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');

        $this->addSql('ALTER TABLE glpi_devicememories CHANGE manufacturers_id manufacturers_id INT DEFAULT NULL, CHANGE devicememorytypes_id devicememorytypes_id INT DEFAULT NULL');


        $this->addSql('ALTER TABLE glpi_devicememories ADD CONSTRAINT FK_7AAE90656145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');

        $this->addSql('ALTER TABLE glpi_devicemotherboards CHANGE manufacturers_id manufacturers_id INT DEFAULT NULL, CHANGE entities_id entities_id INT DEFAULT 0');

        $this->addSql('ALTER TABLE glpi_devicemotherboards ADD CONSTRAINT FK_BA4EEEB76145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');

        $this->addSql('ALTER TABLE glpi_devicenetworkcards CHANGE manufacturers_id manufacturers_id INT DEFAULT NULL, CHANGE entities_id entities_id INT DEFAULT 0');

        $this->addSql('ALTER TABLE glpi_devicenetworkcards ADD CONSTRAINT FK_2F3949626145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');

        $this->addSql('ALTER TABLE glpi_devicepcis CHANGE manufacturers_id manufacturers_id INT DEFAULT NULL, CHANGE devicenetworkcardmodels_id devicenetworkcardmodels_id INT DEFAULT NULL, CHANGE entities_id entities_id INT DEFAULT 0');


        $this->addSql('ALTER TABLE glpi_devicepcis ADD CONSTRAINT FK_754B05616145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');

        $this->addSql('ALTER TABLE glpi_devicepowersupplies CHANGE manufacturers_id manufacturers_id INT DEFAULT NULL, CHANGE entities_id entities_id INT DEFAULT 0');

        $this->addSql('ALTER TABLE glpi_devicepowersupplies ADD CONSTRAINT FK_7C7209ED6145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');

        $this->addSql('ALTER TABLE glpi_deviceprocessors CHANGE manufacturers_id manufacturers_id INT DEFAULT NULL, CHANGE entities_id entities_id INT DEFAULT 0');

        $this->addSql('ALTER TABLE glpi_deviceprocessors ADD CONSTRAINT FK_B6E0F8BB6145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');

        $this->addSql('ALTER TABLE glpi_devicesensors CHANGE devicesensortypes_id devicesensortypes_id INT DEFAULT NULL, CHANGE devicesensormodels_id devicesensormodels_id INT DEFAULT NULL, CHANGE manufacturers_id manufacturers_id INT DEFAULT NULL, CHANGE entities_id entities_id INT DEFAULT 0, CHANGE locations_id locations_id INT DEFAULT NULL, CHANGE states_id states_id INT DEFAULT NULL');



        $this->addSql('ALTER TABLE glpi_devicesensors ADD CONSTRAINT FK_E83286526145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');


        $this->addSql('CREATE INDEX IDX_E83286521B86E75F ON glpi_devicesensors (devicesensormodels_id)');
        $this->addSql('ALTER TABLE glpi_devicesimcards CHANGE entities_id entities_id INT DEFAULT 0, CHANGE manufacturers_id manufacturers_id INT DEFAULT NULL, CHANGE devicesimcardtypes_id devicesimcardtypes_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_devicesimcards ADD CONSTRAINT FK_5A0BB1A86145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');


        $this->addSql('ALTER TABLE glpi_devicesoundcards CHANGE manufacturers_id manufacturers_id INT DEFAULT NULL, CHANGE entities_id entities_id INT DEFAULT 0');

        $this->addSql('ALTER TABLE glpi_devicesoundcards ADD CONSTRAINT FK_D53EF47A6145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');

        $this->addSql('ALTER TABLE glpi_displaypreferences CHANGE users_id users_id INT DEFAULT NULL');

        $this->addSql('CREATE INDEX IDX_67F2BE767B3B43D ON glpi_displaypreferences (users_id)');
        $this->addSql('ALTER TABLE glpi_documentcategories CHANGE documentcategories_id documentcategories_id INT DEFAULT NULL');

        $this->addSql('CREATE INDEX IDX_44E98B1F55AC576F ON glpi_documentcategories (documentcategories_id)');
        $this->addSql('ALTER TABLE glpi_documents CHANGE entities_id entities_id INT DEFAULT 0, CHANGE documentcategories_id documentcategories_id INT DEFAULT NULL, CHANGE users_id users_id INT DEFAULT NULL, CHANGE tickets_id tickets_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_documents ADD CONSTRAINT FK_AF97AD216145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');



        $this->addSql('ALTER TABLE glpi_documents_items CHANGE documents_id documents_id INT DEFAULT NULL, CHANGE entities_id entities_id INT DEFAULT 0, CHANGE users_id users_id INT DEFAULT NULL');

        $this->addSql('ALTER TABLE glpi_documents_items ADD CONSTRAINT FK_DDD24B256145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');

        $this->addSql('CREATE INDEX IDX_DDD24B255F0F2752 ON glpi_documents_items (documents_id)');
        $this->addSql('CREATE INDEX IDX_DDD24B256145D7DB ON glpi_documents_items (entities_id)');
        $this->addSql('ALTER TABLE glpi_domainrecords CHANGE entities_id entities_id INT DEFAULT 0, CHANGE domains_id domains_id INT DEFAULT NULL, CHANGE domainrecordtypes_id domainrecordtypes_id INT DEFAULT NULL, CHANGE users_id_tech users_id_tech INT DEFAULT NULL, CHANGE groups_id_tech groups_id_tech INT DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_domainrecords ADD CONSTRAINT FK_180F59566145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');




        $this->addSql('ALTER TABLE glpi_domainrecordtypes CHANGE entities_id entities_id INT DEFAULT 0');
        $this->addSql('ALTER TABLE glpi_domainrecordtypes ADD CONSTRAINT FK_19DBFAF66145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');
        $this->addSql('CREATE INDEX IDX_19DBFAF66145D7DB ON glpi_domainrecordtypes (entities_id)');
        $this->addSql('ALTER TABLE glpi_domainrelations CHANGE entities_id entities_id INT DEFAULT 0');
        $this->addSql('ALTER TABLE glpi_domainrelations ADD CONSTRAINT FK_29A9192D6145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');
        $this->addSql('CREATE INDEX IDX_29A9192D6145D7DB ON glpi_domainrelations (entities_id)');
        $this->addSql('ALTER TABLE glpi_domains CHANGE entities_id entities_id INT DEFAULT 0, CHANGE domaintypes_id domaintypes_id INT DEFAULT NULL, CHANGE users_id_tech users_id_tech INT DEFAULT NULL, CHANGE groups_id_tech groups_id_tech INT DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_domains ADD CONSTRAINT FK_E64974F96145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');



        $this->addSql('ALTER TABLE glpi_domains_items CHANGE domains_id domains_id INT DEFAULT NULL');


        $this->addSql('ALTER TABLE glpi_domaintypes CHANGE entities_id entities_id INT DEFAULT 0');
        $this->addSql('ALTER TABLE glpi_domaintypes ADD CONSTRAINT FK_C060118E6145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');
        $this->addSql('CREATE INDEX IDX_C060118E6145D7DB ON glpi_domaintypes (entities_id)');
        $this->addSql('ALTER TABLE glpi_enclosures CHANGE entities_id entities_id INT DEFAULT 0, CHANGE locations_id locations_id INT DEFAULT NULL, CHANGE users_id_tech users_id_tech INT DEFAULT NULL, CHANGE groups_id_tech groups_id_tech INT DEFAULT NULL, CHANGE states_id states_id INT DEFAULT NULL COMMENT \'RELATION to states (id)\', CHANGE manufacturers_id manufacturers_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_enclosures ADD CONSTRAINT FK_6052344A6145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');






        $this->addSql('ALTER TABLE glpi_entities CHANGE entities_id entities_id INT DEFAULT 0, CHANGE authldaps_id authldaps_id INT DEFAULT NULL, CHANGE calendars_id calendars_id INT DEFAULT NULL, CHANGE tickettemplates_id tickettemplates_id INT DEFAULT NULL, CHANGE changetemplates_id changetemplates_id INT DEFAULT NULL, CHANGE problemtemplates_id problemtemplates_id INT DEFAULT NULL, CHANGE entities_id_software entities_id_software INT DEFAULT 0');
        $this->addSql('ALTER TABLE glpi_entities ADD CONSTRAINT FK_1A59F36F6145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');






        $this->addSql('CREATE INDEX IDX_1A59F36F7D03EC85 ON glpi_entities (authldaps_id)');
        $this->addSql('CREATE INDEX IDX_1A59F36F72C4B705 ON glpi_entities (calendars_id)');
        $this->addSql('CREATE INDEX IDX_1A59F36F16010B6F ON glpi_entities (entities_id_software)');
        $this->addSql('ALTER TABLE glpi_entities_knowbaseitems CHANGE knowbaseitems_id knowbaseitems_id INT DEFAULT NULL, CHANGE entities_id entities_id INT DEFAULT 0');

        $this->addSql('ALTER TABLE glpi_entities_knowbaseitems ADD CONSTRAINT FK_30391006145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');
        $this->addSql('ALTER TABLE glpi_entities_reminders CHANGE reminders_id reminders_id INT DEFAULT NULL, CHANGE entities_id entities_id INT DEFAULT 0');

        $this->addSql('ALTER TABLE glpi_entities_reminders ADD CONSTRAINT FK_265E93066145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');
        $this->addSql('ALTER TABLE glpi_entities_rssfeeds CHANGE rssfeeds_id rssfeeds_id INT DEFAULT NULL, CHANGE entities_id entities_id INT DEFAULT 0');

        $this->addSql('ALTER TABLE glpi_entities_rssfeeds ADD CONSTRAINT FK_8F946B4A6145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');
        $this->addSql('ALTER TABLE glpi_fieldblacklists CHANGE entities_id entities_id INT DEFAULT 0');
        $this->addSql('ALTER TABLE glpi_fieldblacklists ADD CONSTRAINT FK_2EF3241A6145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');
        $this->addSql('CREATE INDEX IDX_2EF3241A6145D7DB ON glpi_fieldblacklists (entities_id)');
        $this->addSql('ALTER TABLE glpi_fieldunicities CHANGE entities_id entities_id INT DEFAULT 0');
        $this->addSql('ALTER TABLE glpi_fieldunicities ADD CONSTRAINT FK_9CB981EE6145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');
        $this->addSql('CREATE INDEX IDX_9CB981EE6145D7DB ON glpi_fieldunicities (entities_id)');
        $this->addSql('ALTER TABLE glpi_fqdns CHANGE entities_id entities_id INT DEFAULT 0');
        $this->addSql('ALTER TABLE glpi_fqdns ADD CONSTRAINT FK_9D1D670C6145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');
        $this->addSql('ALTER TABLE glpi_groups CHANGE entities_id entities_id INT DEFAULT 0, CHANGE groups_id groups_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_groups ADD CONSTRAINT FK_7286AF616145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');

        $this->addSql('ALTER TABLE glpi_groups_knowbaseitems CHANGE knowbaseitems_id knowbaseitems_id INT DEFAULT NULL, CHANGE groups_id groups_id INT DEFAULT NULL');


        $this->addSql('ALTER TABLE glpi_groups_problems CHANGE problems_id problems_id INT DEFAULT NULL, CHANGE groups_id groups_id INT DEFAULT NULL');


        $this->addSql('CREATE INDEX IDX_35FF34E0E30A47DD ON glpi_groups_problems (problems_id)');
        $this->addSql('CREATE INDEX IDX_35FF34E0F373DCF ON glpi_groups_problems (groups_id)');
        $this->addSql('ALTER TABLE glpi_groups_reminders CHANGE reminders_id reminders_id INT DEFAULT NULL, CHANGE groups_id groups_id INT DEFAULT NULL, CHANGE entities_id entities_id INT DEFAULT 0');


        $this->addSql('ALTER TABLE glpi_groups_reminders ADD CONSTRAINT FK_CB9577E56145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');
        $this->addSql('ALTER TABLE glpi_groups_rssfeeds CHANGE rssfeeds_id rssfeeds_id INT DEFAULT NULL, CHANGE groups_id groups_id INT DEFAULT NULL, CHANGE entities_id entities_id INT DEFAULT 0');


        $this->addSql('ALTER TABLE glpi_groups_rssfeeds ADD CONSTRAINT FK_FCF3A8CA6145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');
        $this->addSql('ALTER TABLE glpi_groups_tickets CHANGE tickets_id tickets_id INT DEFAULT NULL, CHANGE groups_id groups_id INT DEFAULT NULL');


        $this->addSql('CREATE INDEX IDX_C6573B418FDC0E9A ON glpi_groups_tickets (tickets_id)');
        $this->addSql('CREATE INDEX IDX_C6573B41F373DCF ON glpi_groups_tickets (groups_id)');
        $this->addSql('ALTER TABLE glpi_groups_users CHANGE users_id users_id INT DEFAULT NULL, CHANGE groups_id groups_id INT DEFAULT NULL');


        $this->addSql('CREATE INDEX IDX_3023C81467B3B43D ON glpi_groups_users (users_id)');
        $this->addSql('ALTER TABLE glpi_holidays CHANGE entities_id entities_id INT DEFAULT 0');
        $this->addSql('ALTER TABLE glpi_holidays ADD CONSTRAINT FK_70D336866145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');
        $this->addSql('CREATE INDEX IDX_70D336866145D7DB ON glpi_holidays (entities_id)');
        $this->addSql('ALTER TABLE glpi_infocoms CHANGE entities_id entities_id INT DEFAULT 0, CHANGE suppliers_id suppliers_id INT DEFAULT NULL, CHANGE value value NUMERIC(20, 4) DEFAULT \'0\' NOT NULL, CHANGE warranty_value warranty_value NUMERIC(20, 4) DEFAULT \'0\' NOT NULL, CHANGE budgets_id budgets_id INT DEFAULT NULL, CHANGE businesscriticities_id businesscriticities_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_infocoms ADD CONSTRAINT FK_8D32298C6145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');



        $this->addSql('ALTER TABLE glpi_ipaddresses CHANGE entities_id entities_id INT DEFAULT 0');
        $this->addSql('ALTER TABLE glpi_ipaddresses ADD CONSTRAINT FK_563D38B36145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');
        $this->addSql('ALTER TABLE glpi_ipaddresses_ipnetworks CHANGE ipaddresses_id ipaddresses_id INT DEFAULT NULL, CHANGE ipnetworks_id ipnetworks_id INT DEFAULT NULL');


        $this->addSql('ALTER TABLE glpi_ipnetworks CHANGE entities_id entities_id INT DEFAULT 0, CHANGE ipnetworks_id ipnetworks_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_ipnetworks ADD CONSTRAINT FK_2D47D3C86145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');

        $this->addSql('CREATE INDEX IDX_2D47D3C86145D7DB ON glpi_ipnetworks (entities_id)');
        $this->addSql('CREATE INDEX IDX_2D47D3C8A992AA50 ON glpi_ipnetworks (ipnetworks_id)');
        $this->addSql('ALTER TABLE glpi_ipnetworks_vlans CHANGE ipnetworks_id ipnetworks_id INT DEFAULT NULL, CHANGE vlans_id vlans_id INT DEFAULT NULL');


        $this->addSql('CREATE INDEX IDX_35A7AD8AA992AA50 ON glpi_ipnetworks_vlans (ipnetworks_id)');
        $this->addSql('CREATE INDEX IDX_35A7AD8A462B676C ON glpi_ipnetworks_vlans (vlans_id)');
        $this->addSql('ALTER TABLE glpi_itilcategories CHANGE entities_id entities_id INT DEFAULT 0, CHANGE itilcategories_id itilcategories_id INT DEFAULT NULL, CHANGE knowbaseitemcategories_id knowbaseitemcategories_id INT DEFAULT NULL, CHANGE users_id users_id INT DEFAULT NULL, CHANGE groups_id groups_id INT DEFAULT NULL, CHANGE tickettemplates_id_incident tickettemplates_id_incident INT DEFAULT NULL, CHANGE tickettemplates_id_demand tickettemplates_id_demand INT DEFAULT NULL, CHANGE changetemplates_id changetemplates_id INT DEFAULT NULL, CHANGE problemtemplates_id problemtemplates_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_itilcategories ADD CONSTRAINT FK_349D19C46145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');








        $this->addSql('ALTER TABLE glpi_itilfollowups CHANGE users_id users_id INT DEFAULT NULL, CHANGE users_id_editor users_id_editor INT DEFAULT NULL, CHANGE requesttypes_id requesttypes_id INT DEFAULT NULL');



        $this->addSql('ALTER TABLE glpi_itilfollowuptemplates CHANGE entities_id entities_id INT DEFAULT 0, CHANGE requesttypes_id requesttypes_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_itilfollowuptemplates ADD CONSTRAINT FK_3934C13B6145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');

        $this->addSql('ALTER TABLE glpi_itils_projects CHANGE projects_id projects_id INT DEFAULT NULL');

        $this->addSql('ALTER TABLE glpi_itilsolutions CHANGE solutiontypes_id solutiontypes_id INT DEFAULT NULL, CHANGE users_id users_id INT DEFAULT NULL, CHANGE users_id_editor users_id_editor INT DEFAULT NULL, CHANGE users_id_approval users_id_approval INT DEFAULT NULL');





        $this->addSql('ALTER TABLE glpi_knowbaseitemcategories ADD CONSTRAINT FK_60FBD1506145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');
        $this->addSql('ALTER TABLE glpi_knowbaseitems CHANGE knowbaseitemcategories_id knowbaseitemcategories_id INT DEFAULT NULL, CHANGE users_id users_id INT DEFAULT NULL');


        $this->addSql('ALTER TABLE glpi_knowbaseitems_comments CHANGE users_id users_id INT DEFAULT NULL');


        $this->addSql('CREATE INDEX IDX_33AB06316D89C108 ON glpi_knowbaseitems_comments (knowbaseitems_id)');
        $this->addSql('CREATE INDEX IDX_33AB063167B3B43D ON glpi_knowbaseitems_comments (users_id)');
        $this->addSql('ALTER TABLE glpi_knowbaseitems_profiles CHANGE knowbaseitems_id knowbaseitems_id INT DEFAULT NULL, CHANGE profiles_id profiles_id INT DEFAULT NULL, CHANGE entities_id entities_id INT DEFAULT 0');


        $this->addSql('ALTER TABLE glpi_knowbaseitems_profiles ADD CONSTRAINT FK_E705152B6145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');
        $this->addSql('ALTER TABLE glpi_knowbaseitems_revisions CHANGE knowbaseitems_id knowbaseitems_id INT DEFAULT NULL');

        $this->addSql('CREATE INDEX IDX_3B8DEF96D89C108 ON glpi_knowbaseitems_revisions (knowbaseitems_id)');
        $this->addSql('ALTER TABLE glpi_knowbaseitems_users CHANGE knowbaseitems_id knowbaseitems_id INT DEFAULT NULL, CHANGE users_id users_id INT DEFAULT NULL');


        $this->addSql('ALTER TABLE glpi_knowbaseitemtranslations CHANGE knowbaseitems_id knowbaseitems_id INT DEFAULT NULL, CHANGE users_id users_id INT DEFAULT NULL');


        $this->addSql('CREATE INDEX IDX_BF433A936D89C108 ON glpi_knowbaseitemtranslations (knowbaseitems_id)');
        $this->addSql('ALTER TABLE glpi_lineoperators CHANGE entities_id entities_id INT DEFAULT 0');
        $this->addSql('ALTER TABLE glpi_lineoperators ADD CONSTRAINT FK_6E07255A6145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');
        $this->addSql('ALTER TABLE glpi_lines CHANGE entities_id entities_id INT DEFAULT 0, CHANGE users_id users_id INT DEFAULT NULL, CHANGE groups_id groups_id INT DEFAULT NULL, CHANGE lineoperators_id lineoperators_id INT DEFAULT NULL, CHANGE locations_id locations_id INT DEFAULT NULL, CHANGE states_id states_id INT DEFAULT NULL, CHANGE linetypes_id linetypes_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_lines ADD CONSTRAINT FK_AC635CC06145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');






        $this->addSql('CREATE INDEX IDX_AC635CC0F373DCF ON glpi_lines (groups_id)');
        $this->addSql('CREATE INDEX IDX_AC635CC0ED775E23 ON glpi_lines (locations_id)');
        $this->addSql('CREATE INDEX IDX_AC635CC0B17973F ON glpi_lines (states_id)');
        $this->addSql('CREATE INDEX IDX_AC635CC04F5E534D ON glpi_lines (linetypes_id)');
        $this->addSql('ALTER TABLE glpi_links CHANGE entities_id entities_id INT DEFAULT 0');
        $this->addSql('ALTER TABLE glpi_links ADD CONSTRAINT FK_32E0714E6145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');
        $this->addSql('ALTER TABLE glpi_locations CHANGE entities_id entities_id INT DEFAULT 0, CHANGE locations_id locations_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_locations ADD CONSTRAINT FK_1AC195136145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');

        $this->addSql('CREATE INDEX IDX_1AC195136145D7DB ON glpi_locations (entities_id)');
        $this->addSql('ALTER TABLE glpi_monitors CHANGE entities_id entities_id INT DEFAULT 0, CHANGE users_id_tech users_id_tech INT DEFAULT NULL, CHANGE groups_id_tech groups_id_tech INT DEFAULT NULL, CHANGE size size NUMERIC(5, 2) DEFAULT \'0\' NOT NULL, CHANGE locations_id locations_id INT DEFAULT NULL, CHANGE monitortypes_id monitortypes_id INT DEFAULT NULL, CHANGE monitormodels_id monitormodels_id INT DEFAULT NULL, CHANGE manufacturers_id manufacturers_id INT DEFAULT NULL, CHANGE users_id users_id INT DEFAULT NULL, CHANGE groups_id groups_id INT DEFAULT NULL, CHANGE states_id states_id INT DEFAULT NULL, CHANGE ticket_tco ticket_tco NUMERIC(20, 4) DEFAULT \'0\'');
        $this->addSql('ALTER TABLE glpi_monitors ADD CONSTRAINT FK_CC883AB76145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');









        $this->addSql('ALTER TABLE glpi_netpoints CHANGE entities_id entities_id INT DEFAULT 0, CHANGE locations_id locations_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_netpoints ADD CONSTRAINT FK_69DBE45C6145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');

        $this->addSql('CREATE INDEX IDX_69DBE45C6145D7DB ON glpi_netpoints (entities_id)');
        $this->addSql('CREATE INDEX IDX_69DBE45CED775E23 ON glpi_netpoints (locations_id)');
        $this->addSql('ALTER TABLE glpi_networkaliases CHANGE entities_id entities_id INT DEFAULT 0, CHANGE networknames_id networknames_id INT DEFAULT NULL, CHANGE fqdns_id fqdns_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_networkaliases ADD CONSTRAINT FK_4F9E21DC6145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');


        $this->addSql('CREATE INDEX IDX_4F9E21DC6C543AFA ON glpi_networkaliases (fqdns_id)');
        $this->addSql('ALTER TABLE glpi_networkequipments CHANGE entities_id entities_id INT DEFAULT 0, CHANGE users_id_tech users_id_tech INT DEFAULT NULL, CHANGE groups_id_tech groups_id_tech INT DEFAULT NULL, CHANGE locations_id locations_id INT DEFAULT NULL, CHANGE networks_id networks_id INT DEFAULT NULL, CHANGE networkequipmenttypes_id networkequipmenttypes_id INT DEFAULT NULL, CHANGE networkequipmentmodels_id networkequipmentmodels_id INT DEFAULT NULL, CHANGE manufacturers_id manufacturers_id INT DEFAULT NULL, CHANGE users_id users_id INT DEFAULT NULL, CHANGE groups_id groups_id INT DEFAULT NULL, CHANGE states_id states_id INT DEFAULT NULL, CHANGE ticket_tco ticket_tco NUMERIC(20, 4) DEFAULT \'0\'');
        $this->addSql('ALTER TABLE glpi_networkequipments ADD CONSTRAINT FK_AFE59A846145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');










        $this->addSql('ALTER TABLE glpi_networknames CHANGE entities_id entities_id INT DEFAULT 0, CHANGE fqdns_id fqdns_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_networknames ADD CONSTRAINT FK_A148F0756145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');

        $this->addSql('ALTER TABLE glpi_networkportaggregates CHANGE networkports_id networkports_id INT DEFAULT NULL, CHANGE networkports_id_list networkports_id_list INT DEFAULT NULL COMMENT \'array of associated networkports_id\'');


        $this->addSql('CREATE INDEX IDX_88867CD39E2F9770 ON glpi_networkportaggregates (networkports_id_list)');
        $this->addSql('ALTER TABLE glpi_networkportaliases CHANGE networkports_id networkports_id INT DEFAULT NULL, CHANGE networkports_id_alias networkports_id_alias INT DEFAULT NULL');


        $this->addSql('ALTER TABLE glpi_networkportdialups CHANGE networkports_id networkports_id INT DEFAULT NULL');

        $this->addSql('ALTER TABLE glpi_networkportethernets CHANGE networkports_id networkports_id INT DEFAULT NULL, CHANGE netpoints_id netpoints_id INT DEFAULT NULL');


        $this->addSql('ALTER TABLE glpi_networkportfiberchannels CHANGE networkports_id networkports_id INT DEFAULT NULL, CHANGE netpoints_id netpoints_id INT DEFAULT NULL');


        $this->addSql('ALTER TABLE glpi_networkportlocals CHANGE networkports_id networkports_id INT DEFAULT NULL');

        $this->addSql('ALTER TABLE glpi_networkports CHANGE entities_id entities_id INT DEFAULT 0');
        $this->addSql('ALTER TABLE glpi_networkports ADD CONSTRAINT FK_DAE469F86145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');
        $this->addSql('ALTER TABLE glpi_networkports_networkports CHANGE networkports_id_1 networkports_id_1 INT DEFAULT NULL, CHANGE networkports_id_2 networkports_id_2 INT DEFAULT NULL');


        $this->addSql('CREATE INDEX IDX_DF0512CAF88ABD1B ON glpi_networkports_networkports (networkports_id_1)');
        $this->addSql('ALTER TABLE glpi_networkports_vlans CHANGE networkports_id networkports_id INT DEFAULT NULL, CHANGE vlans_id vlans_id INT DEFAULT NULL');


        $this->addSql('CREATE INDEX IDX_84FF692CCE45BD77 ON glpi_networkports_vlans (networkports_id)');
        $this->addSql('ALTER TABLE glpi_networkportwifis CHANGE networkports_id networkports_id INT DEFAULT NULL, CHANGE wifinetworks_id wifinetworks_id INT DEFAULT NULL, CHANGE networkportwifis_id networkportwifis_id INT DEFAULT NULL COMMENT \'only useful in case of Managed node\'');



        $this->addSql('CREATE INDEX IDX_FB43456A4D4D852B ON glpi_networkportwifis (networkportwifis_id)');
        $this->addSql('ALTER TABLE glpi_notepads CHANGE users_id users_id INT DEFAULT NULL');


        $this->addSql('ALTER TABLE glpi_notifications CHANGE entities_id entities_id INT DEFAULT 0');
        $this->addSql('ALTER TABLE glpi_notifications ADD CONSTRAINT FK_72C25A896145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');
        $this->addSql('ALTER TABLE glpi_notifications_notificationtemplates CHANGE notifications_id notifications_id INT DEFAULT NULL, CHANGE notificationtemplates_id notificationtemplates_id INT DEFAULT NULL');


        $this->addSql('ALTER TABLE glpi_notificationtargets CHANGE notifications_id notifications_id INT DEFAULT NULL');

        $this->addSql('ALTER TABLE glpi_notificationtemplatetranslations CHANGE notificationtemplates_id notificationtemplates_id INT DEFAULT NULL');

        $this->addSql('ALTER TABLE glpi_notimportedemails CHANGE mailcollectors_id mailcollectors_id INT DEFAULT NULL, CHANGE users_id users_id INT DEFAULT NULL');


        $this->addSql('ALTER TABLE glpi_objectlocks CHANGE users_id users_id INT DEFAULT NULL');

        $this->addSql('CREATE INDEX IDX_55A8E45D67B3B43D ON glpi_objectlocks (users_id)');
        $this->addSql('ALTER TABLE glpi_olalevelactions CHANGE olalevels_id olalevels_id INT DEFAULT NULL');

        $this->addSql('ALTER TABLE glpi_olalevelcriterias CHANGE olalevels_id olalevels_id INT DEFAULT NULL');

        $this->addSql('ALTER TABLE glpi_olalevels CHANGE olas_id olas_id INT DEFAULT NULL, CHANGE entities_id entities_id INT DEFAULT 0');

        $this->addSql('ALTER TABLE glpi_olalevels ADD CONSTRAINT FK_EC99B26D6145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');
        $this->addSql('CREATE INDEX IDX_EC99B26D6145D7DB ON glpi_olalevels (entities_id)');
        $this->addSql('ALTER TABLE glpi_olalevels_tickets CHANGE tickets_id tickets_id INT DEFAULT NULL, CHANGE olalevels_id olalevels_id INT DEFAULT NULL');


        $this->addSql('ALTER TABLE glpi_olas CHANGE entities_id entities_id INT DEFAULT 0, CHANGE calendars_id calendars_id INT DEFAULT NULL, CHANGE slms_id slms_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_olas ADD CONSTRAINT FK_B7FD34E56145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');


        $this->addSql('CREATE INDEX IDX_B7FD34E56145D7DB ON glpi_olas (entities_id)');
        $this->addSql('ALTER TABLE glpi_operatingsystemkernelversions CHANGE operatingsystemkernels_id operatingsystemkernels_id INT DEFAULT NULL');

        $this->addSql('ALTER TABLE glpi_passivedcequipments CHANGE entities_id entities_id INT DEFAULT 0, CHANGE locations_id locations_id INT DEFAULT NULL, CHANGE passivedcequipmenttypes_id passivedcequipmenttypes_id INT DEFAULT NULL, CHANGE users_id_tech users_id_tech INT DEFAULT NULL, CHANGE groups_id_tech groups_id_tech INT DEFAULT NULL, CHANGE states_id states_id INT DEFAULT NULL, CHANGE manufacturers_id manufacturers_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_passivedcequipments ADD CONSTRAINT FK_3CF108C66145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');







        $this->addSql('ALTER TABLE glpi_pdus CHANGE entities_id entities_id INT DEFAULT 0, CHANGE locations_id locations_id INT DEFAULT NULL, CHANGE groups_id_tech groups_id_tech INT DEFAULT NULL, CHANGE states_id states_id INT DEFAULT NULL, CHANGE manufacturers_id manufacturers_id INT DEFAULT NULL, CHANGE pdutypes_id pdutypes_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_pdus ADD CONSTRAINT FK_9F3AF5C16145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');







        $this->addSql('ALTER TABLE glpi_pdus_plugs CHANGE plugs_id plugs_id INT DEFAULT NULL, CHANGE pdus_id pdus_id INT DEFAULT NULL');


        $this->addSql('ALTER TABLE glpi_pdus_racks CHANGE racks_id racks_id INT DEFAULT NULL, CHANGE pdus_id pdus_id INT DEFAULT NULL');


        $this->addSql('ALTER TABLE glpi_pdutypes CHANGE entities_id entities_id INT DEFAULT 0');
        $this->addSql('ALTER TABLE glpi_pdutypes ADD CONSTRAINT FK_38C353DA6145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');
        $this->addSql('ALTER TABLE glpi_peripherals CHANGE entities_id entities_id INT DEFAULT 0, CHANGE users_id_tech users_id_tech INT DEFAULT NULL, CHANGE groups_id_tech groups_id_tech INT DEFAULT NULL, CHANGE locations_id locations_id INT DEFAULT NULL, CHANGE peripheraltypes_id peripheraltypes_id INT DEFAULT NULL, CHANGE peripheralmodels_id peripheralmodels_id INT DEFAULT NULL, CHANGE manufacturers_id manufacturers_id INT DEFAULT NULL, CHANGE users_id users_id INT DEFAULT NULL, CHANGE groups_id groups_id INT DEFAULT NULL, CHANGE states_id states_id INT DEFAULT NULL, CHANGE ticket_tco ticket_tco NUMERIC(20, 4) DEFAULT \'0\'');
        $this->addSql('ALTER TABLE glpi_peripherals ADD CONSTRAINT FK_B49D1266145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');









        $this->addSql('ALTER TABLE glpi_phones CHANGE users_id_tech users_id_tech INT DEFAULT NULL, CHANGE groups_id_tech groups_id_tech INT DEFAULT NULL, CHANGE locations_id locations_id INT DEFAULT NULL, CHANGE phonetypes_id phonetypes_id INT DEFAULT NULL, CHANGE phonemodels_id phonemodels_id INT DEFAULT NULL, CHANGE phonepowersupplies_id phonepowersupplies_id INT DEFAULT NULL, CHANGE manufacturers_id manufacturers_id INT DEFAULT NULL, CHANGE users_id users_id INT DEFAULT NULL, CHANGE groups_id groups_id INT DEFAULT NULL, CHANGE states_id states_id INT DEFAULT NULL, CHANGE ticket_tco ticket_tco NUMERIC(20, 4) DEFAULT \'0\'');










        $this->addSql('ALTER TABLE glpi_planningexternalevents CHANGE entities_id entities_id INT DEFAULT 0, CHANGE users_id_guests users_id_guests INT DEFAULT NULL, CHANGE groups_id groups_id INT DEFAULT NULL, CHANGE planningeventcategories_id planningeventcategories_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_planningexternalevents ADD CONSTRAINT FK_544F3E8E6145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');




        $this->addSql('CREATE INDEX IDX_544F3E8ED5B73BE ON glpi_planningexternalevents (users_id_guests)');
        $this->addSql('ALTER TABLE glpi_planningexternaleventtemplates CHANGE entities_id entities_id INT DEFAULT 0, CHANGE planningeventcategories_id planningeventcategories_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_planningexternaleventtemplates ADD CONSTRAINT FK_A85DD10C6145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');

        $this->addSql('ALTER TABLE glpi_planningrecalls CHANGE users_id users_id INT DEFAULT NULL');

        $this->addSql('ALTER TABLE glpi_printers CHANGE entities_id entities_id INT DEFAULT 0, CHANGE users_id_tech users_id_tech INT DEFAULT NULL, CHANGE groups_id_tech groups_id_tech INT DEFAULT NULL, CHANGE locations_id locations_id INT DEFAULT NULL, CHANGE networks_id networks_id INT DEFAULT NULL, CHANGE printertypes_id printertypes_id INT DEFAULT NULL, CHANGE printermodels_id printermodels_id INT DEFAULT NULL, CHANGE manufacturers_id manufacturers_id INT DEFAULT NULL, CHANGE users_id users_id INT DEFAULT NULL, CHANGE groups_id groups_id INT DEFAULT NULL, CHANGE states_id states_id INT DEFAULT NULL, CHANGE ticket_tco ticket_tco NUMERIC(20, 4) DEFAULT \'0\'');
        $this->addSql('ALTER TABLE glpi_printers ADD CONSTRAINT FK_8F8D8A3D6145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');










        $this->addSql('ALTER TABLE glpi_problemcosts CHANGE problems_id problems_id INT DEFAULT NULL, CHANGE cost_time cost_time NUMERIC(20, 4) DEFAULT \'0\' NOT NULL, CHANGE cost_fixed cost_fixed NUMERIC(20, 4) DEFAULT \'0\' NOT NULL, CHANGE cost_material cost_material NUMERIC(20, 4) DEFAULT \'0\' NOT NULL, CHANGE budgets_id budgets_id INT DEFAULT NULL, CHANGE entities_id entities_id INT DEFAULT 0');


        $this->addSql('ALTER TABLE glpi_problemcosts ADD CONSTRAINT FK_5D343AA86145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');
        $this->addSql('ALTER TABLE glpi_problems CHANGE entities_id entities_id INT DEFAULT 0, CHANGE users_id_recipient users_id_recipient INT DEFAULT NULL, CHANGE users_id_lastupdater users_id_lastupdater INT DEFAULT NULL, CHANGE itilcategories_id itilcategories_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_problems ADD CONSTRAINT FK_C4D3F5CF6145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');



        $this->addSql('ALTER TABLE glpi_problems_suppliers CHANGE problems_id problems_id INT DEFAULT NULL, CHANGE suppliers_id suppliers_id INT DEFAULT NULL');


        $this->addSql('CREATE INDEX IDX_4A101DFE30A47DD ON glpi_problems_suppliers (problems_id)');
        $this->addSql('CREATE INDEX IDX_4A101DF355AF43 ON glpi_problems_suppliers (suppliers_id)');
        $this->addSql('ALTER TABLE glpi_problems_tickets CHANGE problems_id problems_id INT DEFAULT NULL, CHANGE tickets_id tickets_id INT DEFAULT NULL');


        $this->addSql('CREATE INDEX IDX_3DF11CF6E30A47DD ON glpi_problems_tickets (problems_id)');
        $this->addSql('ALTER TABLE glpi_problems_users CHANGE problems_id problems_id INT DEFAULT NULL, CHANGE users_id users_id INT DEFAULT NULL');


        $this->addSql('CREATE INDEX IDX_5C9612D2E30A47DD ON glpi_problems_users (problems_id)');
        $this->addSql('CREATE INDEX IDX_5C9612D267B3B43D ON glpi_problems_users (users_id)');
        $this->addSql('ALTER TABLE glpi_problemtasks CHANGE problems_id problems_id INT DEFAULT NULL, CHANGE taskcategories_id taskcategories_id INT DEFAULT NULL, CHANGE users_id users_id INT DEFAULT NULL, CHANGE users_id_editor users_id_editor INT DEFAULT NULL, CHANGE users_id_tech users_id_tech INT DEFAULT NULL, CHANGE groups_id_tech groups_id_tech INT DEFAULT NULL, CHANGE tasktemplates_id tasktemplates_id INT DEFAULT NULL');







        $this->addSql('ALTER TABLE glpi_problemtemplatehiddenfields CHANGE problemtemplates_id problemtemplates_id INT DEFAULT NULL');

        $this->addSql('ALTER TABLE glpi_problemtemplatemandatoryfields CHANGE problemtemplates_id problemtemplates_id INT DEFAULT NULL');

        $this->addSql('ALTER TABLE glpi_problemtemplatepredefinedfields CHANGE problemtemplates_id problemtemplates_id INT DEFAULT NULL');

        $this->addSql('ALTER TABLE glpi_problemtemplates CHANGE entities_id entities_id INT DEFAULT 0');
        $this->addSql('ALTER TABLE glpi_problemtemplates ADD CONSTRAINT FK_38D229266145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');
        $this->addSql('ALTER TABLE glpi_profilerights CHANGE profiles_id profiles_id INT DEFAULT NULL');

        $this->addSql('CREATE INDEX IDX_6E4E481722077C89 ON glpi_profilerights (profiles_id)');
        $this->addSql('ALTER TABLE glpi_profiles CHANGE tickettemplates_id tickettemplates_id INT DEFAULT NULL, CHANGE changetemplates_id changetemplates_id INT DEFAULT NULL, CHANGE problemtemplates_id problemtemplates_id INT DEFAULT NULL');



        $this->addSql('ALTER TABLE glpi_profiles_reminders CHANGE reminders_id reminders_id INT DEFAULT NULL, CHANGE profiles_id profiles_id INT DEFAULT NULL, CHANGE entities_id entities_id INT DEFAULT 0');


        $this->addSql('ALTER TABLE glpi_profiles_reminders ADD CONSTRAINT FK_4A5D764F6145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');
        $this->addSql('ALTER TABLE glpi_profiles_rssfeeds CHANGE rssfeeds_id rssfeeds_id INT DEFAULT NULL, CHANGE profiles_id profiles_id INT DEFAULT NULL');


        $this->addSql('ALTER TABLE glpi_profiles_users CHANGE users_id users_id INT DEFAULT NULL, CHANGE profiles_id profiles_id INT DEFAULT NULL, CHANGE entities_id entities_id INT DEFAULT 0');


        $this->addSql('ALTER TABLE glpi_profiles_users ADD CONSTRAINT FK_752007FA6145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');
        $this->addSql('ALTER TABLE glpi_projectcosts CHANGE projects_id projects_id INT DEFAULT NULL, CHANGE cost cost NUMERIC(20, 4) DEFAULT \'0\' NOT NULL, CHANGE budgets_id budgets_id INT DEFAULT NULL, CHANGE entities_id entities_id INT DEFAULT 0');


        $this->addSql('ALTER TABLE glpi_projectcosts ADD CONSTRAINT FK_BEAAE5F26145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');
        $this->addSql('ALTER TABLE glpi_projects CHANGE entities_id entities_id INT DEFAULT 0, CHANGE projects_id projects_id INT DEFAULT NULL, CHANGE projectstates_id projectstates_id INT DEFAULT NULL, CHANGE projecttypes_id projecttypes_id INT DEFAULT NULL, CHANGE users_id users_id INT DEFAULT NULL, CHANGE groups_id groups_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_projects ADD CONSTRAINT FK_1626242E6145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');





        $this->addSql('ALTER TABLE glpi_projecttasks CHANGE entities_id entities_id INT DEFAULT 0, CHANGE projects_id projects_id INT DEFAULT NULL, CHANGE projecttasks_id projecttasks_id INT DEFAULT NULL, CHANGE projectstates_id projectstates_id INT DEFAULT NULL, CHANGE projecttasktypes_id projecttasktypes_id INT DEFAULT NULL, CHANGE users_id users_id INT DEFAULT NULL, CHANGE projecttasktemplates_id projecttasktemplates_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_projecttasks ADD CONSTRAINT FK_41EFD7CD6145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');






        $this->addSql('ALTER TABLE glpi_projecttasks_tickets CHANGE tickets_id tickets_id INT DEFAULT NULL, CHANGE projecttasks_id projecttasks_id INT DEFAULT NULL');


        $this->addSql('CREATE INDEX IDX_2D48CB0A8FDC0E9A ON glpi_projecttasks_tickets (tickets_id)');
        $this->addSql('ALTER TABLE glpi_projecttaskteams CHANGE projecttasks_id projecttasks_id INT DEFAULT NULL');

        $this->addSql('CREATE INDEX IDX_1B0A1B0D171C029 ON glpi_projecttaskteams (projecttasks_id)');
        $this->addSql('ALTER TABLE glpi_projecttasktemplates CHANGE entities_id entities_id INT DEFAULT 0, CHANGE projects_id projects_id INT DEFAULT NULL, CHANGE projecttasks_id projecttasks_id INT DEFAULT NULL, CHANGE projectstates_id projectstates_id INT DEFAULT NULL, CHANGE projecttasktypes_id projecttasktypes_id INT DEFAULT NULL, CHANGE users_id users_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_projecttasktemplates ADD CONSTRAINT FK_286BFEDA6145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');





        $this->addSql('ALTER TABLE glpi_projectteams CHANGE projects_id projects_id INT DEFAULT NULL');

        $this->addSql('CREATE INDEX IDX_877590021EDE0F55 ON glpi_projectteams (projects_id)');
        $this->addSql('ALTER TABLE glpi_queuedchats CHANGE notificationtemplates_id notificationtemplates_id INT DEFAULT NULL, CHANGE entities_id entities_id INT DEFAULT 0, CHANGE locations_id locations_id INT DEFAULT NULL, CHANGE groups_id groups_id INT DEFAULT NULL, CHANGE itilcategories_id itilcategories_id INT DEFAULT NULL');

        $this->addSql('ALTER TABLE glpi_queuedchats ADD CONSTRAINT FK_7E072DC26145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');



        $this->addSql('CREATE INDEX IDX_7E072DC2A9E8DD2B ON glpi_queuedchats (notificationtemplates_id)');
        $this->addSql('CREATE INDEX IDX_7E072DC2ED775E23 ON glpi_queuedchats (locations_id)');
        $this->addSql('CREATE INDEX IDX_7E072DC2F373DCF ON glpi_queuedchats (groups_id)');
        $this->addSql('CREATE INDEX IDX_7E072DC2EFE9C34D ON glpi_queuedchats (itilcategories_id)');
        $this->addSql('ALTER TABLE glpi_queuednotifications CHANGE notificationtemplates_id notificationtemplates_id INT DEFAULT NULL, CHANGE entities_id entities_id INT DEFAULT 0');

        $this->addSql('ALTER TABLE glpi_queuednotifications ADD CONSTRAINT FK_FDE960546145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');
        $this->addSql('CREATE INDEX IDX_FDE96054A9E8DD2B ON glpi_queuednotifications (notificationtemplates_id)');
        $this->addSql('ALTER TABLE glpi_racks CHANGE entities_id entities_id INT DEFAULT 0, CHANGE locations_id locations_id INT DEFAULT NULL, CHANGE manufacturers_id manufacturers_id INT DEFAULT NULL, CHANGE racktypes_id racktypes_id INT DEFAULT NULL, CHANGE states_id states_id INT DEFAULT NULL, CHANGE users_id_tech users_id_tech INT DEFAULT NULL, CHANGE groups_id_tech groups_id_tech INT DEFAULT NULL, CHANGE dcrooms_id dcrooms_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_racks ADD CONSTRAINT FK_205CE3116145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');








        $this->addSql('ALTER TABLE glpi_racktypes CHANGE entities_id entities_id INT DEFAULT 0');
        $this->addSql('ALTER TABLE glpi_racktypes ADD CONSTRAINT FK_6E4557BD6145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');
        $this->addSql('ALTER TABLE glpi_reminders CHANGE users_id users_id INT DEFAULT NULL');

        $this->addSql('ALTER TABLE glpi_reminders_users CHANGE reminders_id reminders_id INT DEFAULT NULL, CHANGE users_id users_id INT DEFAULT NULL');


        $this->addSql('ALTER TABLE glpi_remindertranslations CHANGE reminders_id reminders_id INT DEFAULT NULL, CHANGE users_id users_id INT DEFAULT NULL');


        $this->addSql('CREATE INDEX IDX_BE66B0AAC7C7BF28 ON glpi_remindertranslations (reminders_id)');
        $this->addSql('ALTER TABLE glpi_reservationitems CHANGE entities_id entities_id INT DEFAULT 0');
        $this->addSql('ALTER TABLE glpi_reservationitems ADD CONSTRAINT FK_1AD247B06145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');
        $this->addSql('ALTER TABLE glpi_reservations CHANGE reservationitems_id reservationitems_id INT DEFAULT NULL, CHANGE users_id users_id INT DEFAULT NULL');


        $this->addSql('ALTER TABLE glpi_rssfeeds CHANGE users_id users_id INT DEFAULT NULL');

        $this->addSql('ALTER TABLE glpi_rssfeeds_users CHANGE rssfeeds_id rssfeeds_id INT DEFAULT NULL, CHANGE users_id users_id INT DEFAULT NULL');


        $this->addSql('ALTER TABLE glpi_ruleactions CHANGE rules_id rules_id INT DEFAULT NULL');

        $this->addSql('ALTER TABLE glpi_rulecriterias CHANGE rules_id rules_id INT DEFAULT NULL');

        $this->addSql('ALTER TABLE glpi_rules CHANGE entities_id entities_id INT DEFAULT 0');
        $this->addSql('ALTER TABLE glpi_rules ADD CONSTRAINT FK_6AF8496A6145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');
        $this->addSql('ALTER TABLE glpi_savedsearches CHANGE users_id users_id INT DEFAULT NULL, CHANGE entities_id entities_id INT DEFAULT 0');

        $this->addSql('ALTER TABLE glpi_savedsearches ADD CONSTRAINT FK_8C93FCA96145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');
        $this->addSql('ALTER TABLE glpi_savedsearches_alerts CHANGE savedsearches_id savedsearches_id INT DEFAULT NULL');

        $this->addSql('CREATE INDEX IDX_8F033C74D137DC92 ON glpi_savedsearches_alerts (savedsearches_id)');
        $this->addSql('ALTER TABLE glpi_savedsearches_users CHANGE users_id users_id INT DEFAULT NULL, CHANGE savedsearches_id savedsearches_id INT DEFAULT NULL');


        $this->addSql('CREATE INDEX IDX_6AB618A167B3B43D ON glpi_savedsearches_users (users_id)');
        $this->addSql('ALTER TABLE glpi_slalevelactions CHANGE slalevels_id slalevels_id INT DEFAULT NULL');

        $this->addSql('ALTER TABLE glpi_slalevelcriterias CHANGE slalevels_id slalevels_id INT DEFAULT NULL');

        $this->addSql('ALTER TABLE glpi_slalevels CHANGE slas_id slas_id INT DEFAULT NULL, CHANGE entities_id entities_id INT DEFAULT 0');

        $this->addSql('ALTER TABLE glpi_slalevels ADD CONSTRAINT FK_A66D03086145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');
        $this->addSql('CREATE INDEX IDX_A66D03086145D7DB ON glpi_slalevels (entities_id)');
        $this->addSql('ALTER TABLE glpi_slalevels_tickets CHANGE tickets_id tickets_id INT DEFAULT NULL, CHANGE slalevels_id slalevels_id INT DEFAULT NULL');


        $this->addSql('ALTER TABLE glpi_slas CHANGE entities_id entities_id INT DEFAULT 0, CHANGE calendars_id calendars_id INT DEFAULT NULL, CHANGE slms_id slms_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_slas ADD CONSTRAINT FK_AD32DCC26145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');


        $this->addSql('CREATE INDEX IDX_AD32DCC26145D7DB ON glpi_slas (entities_id)');
        $this->addSql('ALTER TABLE glpi_slms CHANGE entities_id entities_id INT DEFAULT 0, CHANGE calendars_id calendars_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_slms ADD CONSTRAINT FK_18793CE6145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');

        $this->addSql('ALTER TABLE glpi_softwarecategories CHANGE softwarecategories_id softwarecategories_id INT DEFAULT NULL');

        $this->addSql('ALTER TABLE glpi_softwarelicenses CHANGE softwares_id softwares_id INT DEFAULT NULL, CHANGE softwarelicenses_id softwarelicenses_id INT DEFAULT NULL, CHANGE entities_id entities_id INT DEFAULT 0, CHANGE softwarelicensetypes_id softwarelicensetypes_id INT DEFAULT NULL, CHANGE softwareversions_id_buy softwareversions_id_buy INT DEFAULT NULL, CHANGE locations_id locations_id INT DEFAULT NULL, CHANGE users_id_tech users_id_tech INT DEFAULT NULL, CHANGE groups_id_tech groups_id_tech INT DEFAULT NULL, CHANGE states_id states_id INT DEFAULT NULL, CHANGE manufacturers_id manufacturers_id INT DEFAULT NULL');


        $this->addSql('ALTER TABLE glpi_softwarelicenses ADD CONSTRAINT FK_8DF16B586145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');









        $this->addSql('CREATE INDEX IDX_8DF16B58E67D8904 ON glpi_softwarelicenses (softwares_id)');
        $this->addSql('CREATE INDEX IDX_8DF16B5844CA6F2F ON glpi_softwarelicenses (softwarelicenses_id)');
        $this->addSql('ALTER TABLE glpi_softwarelicensetypes CHANGE softwarelicensetypes_id softwarelicensetypes_id INT DEFAULT NULL, CHANGE entities_id entities_id INT DEFAULT 0');

        $this->addSql('ALTER TABLE glpi_softwarelicensetypes ADD CONSTRAINT FK_D4B117C36145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');
        $this->addSql('CREATE INDEX IDX_D4B117C36145D7DB ON glpi_softwarelicensetypes (entities_id)');
        $this->addSql('ALTER TABLE glpi_softwares CHANGE entities_id entities_id INT DEFAULT 0, CHANGE locations_id locations_id INT DEFAULT NULL, CHANGE users_id_tech users_id_tech INT DEFAULT NULL, CHANGE groups_id_tech groups_id_tech INT DEFAULT NULL, CHANGE softwares_id softwares_id INT DEFAULT NULL, CHANGE manufacturers_id manufacturers_id INT DEFAULT NULL, CHANGE users_id users_id INT DEFAULT NULL, CHANGE groups_id groups_id INT DEFAULT NULL, CHANGE ticket_tco ticket_tco NUMERIC(20, 4) DEFAULT \'0\'');
        $this->addSql('ALTER TABLE glpi_softwares ADD CONSTRAINT FK_1D851FEB6145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');







        $this->addSql('ALTER TABLE glpi_softwareversions CHANGE entities_id entities_id INT DEFAULT 0, CHANGE softwares_id softwares_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_softwareversions ADD CONSTRAINT FK_EB1F24B56145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');


        $this->addSql('ALTER TABLE glpi_solutiontemplates CHANGE entities_id entities_id INT DEFAULT 0, CHANGE solutiontypes_id solutiontypes_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_solutiontemplates ADD CONSTRAINT FK_6048BE7A6145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');

        $this->addSql('ALTER TABLE glpi_solutiontypes CHANGE entities_id entities_id INT DEFAULT 0');
        $this->addSql('ALTER TABLE glpi_solutiontypes ADD CONSTRAINT FK_B819008A6145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');
        $this->addSql('ALTER TABLE glpi_states CHANGE entities_id entities_id INT DEFAULT 0, CHANGE states_id states_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_states ADD CONSTRAINT FK_B329E15C6145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');

        $this->addSql('CREATE INDEX IDX_B329E15C6145D7DB ON glpi_states (entities_id)');
        $this->addSql('CREATE INDEX IDX_B329E15CB17973F ON glpi_states (states_id)');
        $this->addSql('ALTER TABLE glpi_suppliers CHANGE entities_id entities_id INT DEFAULT 0, CHANGE suppliertypes_id suppliertypes_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_suppliers ADD CONSTRAINT FK_A10F66F56145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');

        $this->addSql('ALTER TABLE glpi_suppliers_tickets CHANGE tickets_id tickets_id INT DEFAULT NULL, CHANGE suppliers_id suppliers_id INT DEFAULT NULL');


        $this->addSql('CREATE INDEX IDX_C3F21B8B8FDC0E9A ON glpi_suppliers_tickets (tickets_id)');
        $this->addSql('CREATE INDEX IDX_C3F21B8B355AF43 ON glpi_suppliers_tickets (suppliers_id)');
        $this->addSql('ALTER TABLE glpi_taskcategories CHANGE entities_id entities_id INT DEFAULT 0, CHANGE taskcategories_id taskcategories_id INT DEFAULT NULL, CHANGE knowbaseitemcategories_id knowbaseitemcategories_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_taskcategories ADD CONSTRAINT FK_83E950246145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');


        $this->addSql('ALTER TABLE glpi_tasktemplates CHANGE entities_id entities_id INT DEFAULT 0, CHANGE taskcategories_id taskcategories_id INT DEFAULT NULL, CHANGE users_id_tech users_id_tech INT DEFAULT NULL, CHANGE groups_id_tech groups_id_tech INT DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_tasktemplates ADD CONSTRAINT FK_13EA38F86145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');



        $this->addSql('ALTER TABLE glpi_ticketcosts CHANGE tickets_id tickets_id INT DEFAULT NULL, CHANGE cost_time cost_time NUMERIC(20, 4) DEFAULT \'0\' NOT NULL, CHANGE cost_fixed cost_fixed NUMERIC(20, 4) DEFAULT \'0\' NOT NULL, CHANGE cost_material cost_material NUMERIC(20, 4) DEFAULT \'0\' NOT NULL, CHANGE budgets_id budgets_id INT DEFAULT NULL, CHANGE entities_id entities_id INT DEFAULT 0');


        $this->addSql('ALTER TABLE glpi_ticketcosts ADD CONSTRAINT FK_A94AF7496145D7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');
        $this->addSql('ALTER TABLE glpi_tickets ADD CONSTRAINT FK_ticketEntitiesD7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');
        $this->addSql('ALTER TABLE glpi_tickets ADD CONSTRAINT FK_ticketRequesttypesD7DB FOREIGN KEY (requesttypes_id) REFERENCES glpi_requesttypes (id)');
        $this->addSql('ALTER TABLE glpi_tickets ADD CONSTRAINT FK_ticketItilcategoriesD7DB FOREIGN KEY (itilcategories_id) REFERENCES glpi_itilcategories (id)');
        $this->addSql('ALTER TABLE glpi_tickets ADD CONSTRAINT FK_ticketLocationsD7DB FOREIGN KEY (locations_id) REFERENCES glpi_locations (id)');

        $this->addSql('ALTER TABLE glpi_ticketrecurrents ADD CONSTRAINT FK_ticketRecurrentEntitiesD7DB FOREIGN KEY (entities_id) REFERENCES glpi_ticketrecurrents (id)');
        $this->addSql('ALTER TABLE glpi_ticketrecurrents ADD CONSTRAINT FK_ticketRecurrentTickettemplatesD7DB FOREIGN KEY (tickettemplates_id) REFERENCES glpi_tickettemplates (id)');
        $this->addSql('ALTER TABLE glpi_ticketrecurrents ADD CONSTRAINT FK_ticketRecurrentCalendarsD7DB FOREIGN KEY (calendars_id) REFERENCES glpi_calendars (id)');
        $this->addSql('ALTER TABLE glpi_tickettasks ADD CONSTRAINT FK_tickettasksTicketD7DB FOREIGN KEY (tickets_id) REFERENCES glpi_tickets (id)');
        $this->addSql('ALTER TABLE glpi_tickettasks ADD CONSTRAINT FK_tickettasksTaskscategoriesD7DB FOREIGN KEY (taskcategories_id) REFERENCES glpi_taskcategories (id)');
        $this->addSql('ALTER TABLE glpi_tickettasks ADD CONSTRAINT FK_tickettasksUsersD7DB FOREIGN KEY (users_id) REFERENCES glpi_users (id)');
        $this->addSql('ALTER TABLE glpi_tickettasks ADD CONSTRAINT FK_tickettasksUsersEditorD7DB FOREIGN KEY (users_id_editor) REFERENCES glpi_users (id)');
        $this->addSql('ALTER TABLE glpi_tickettasks ADD CONSTRAINT FK_tickettasksUsersTechD7DB FOREIGN KEY (users_id_tech) REFERENCES glpi_users (id)');
        $this->addSql('ALTER TABLE glpi_tickettasks ADD CONSTRAINT FK_tickettasksGroupsTechD7DB FOREIGN KEY (groups_id_tech) REFERENCES glpi_groups (id)');
        $this->addSql('ALTER TABLE glpi_tickettasks ADD CONSTRAINT FK_tickettasksTasktemplateD7DB FOREIGN KEY (tasktemplates_id) REFERENCES glpi_tasktemplates (id)');
        $this->addSql('ALTER TABLE glpi_users ADD CONSTRAINT FK_usersLocationsD7DB FOREIGN KEY (locations_id) REFERENCES glpi_locations (id)');
        $this->addSql('ALTER TABLE glpi_users ADD CONSTRAINT FK_usersProfilesD7DB FOREIGN KEY (profiles_id) REFERENCES glpi_profiles (id)');
        $this->addSql('ALTER TABLE glpi_users ADD CONSTRAINT FK_usersEntitiesD7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');
        $this->addSql('ALTER TABLE glpi_users ADD CONSTRAINT FK_usersUserstitlesD7DB FOREIGN KEY (usertitles_id) REFERENCES glpi_usertitles (id)');
        $this->addSql('ALTER TABLE glpi_users ADD CONSTRAINT FK_usersUsercategoriesD7DB FOREIGN KEY (usercategories_id) REFERENCES glpi_usercategories (id)');
        $this->addSql('ALTER TABLE glpi_users ADD CONSTRAINT FK_usersGroupsD7DB FOREIGN KEY (groups_id) REFERENCES glpi_groups (id)');
        $this->addSql('ALTER TABLE glpi_users ADD CONSTRAINT FK_usersDefaultRequestTypeD7DB FOREIGN KEY (default_requesttypes_id) REFERENCES glpi_requesttypes (id)');

        $this->addSql('ALTER TABLE glpi_useremails ADD CONSTRAINT FK_usersemailsUsersD7DB FOREIGN KEY (users_id) REFERENCES glpi_users (id)');
        $this->addSql('ALTER TABLE glpi_vlans ADD CONSTRAINT FK_vlansEntitiesD7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');
        $this->addSql('ALTER TABLE glpi_wifinetworks ADD CONSTRAINT FK_wifinetworksEntitiesD7DB FOREIGN KEY (entities_id) REFERENCES glpi_entities (id)');

        $this->addSql('ALTER TABLE glpi_users CHANGE language language VARCHAR(10) DEFAULT NULL COMMENT \'see define.php CFG_GLPI[language] array\', CHANGE csv_delimiter csv_delimiter VARCHAR(1) DEFAULT NULL, CHANGE priority_1 priority_1 VARCHAR(20) DEFAULT NULL, CHANGE priority_2 priority_2 VARCHAR(20) DEFAULT NULL, CHANGE priority_3 priority_3 VARCHAR(20) DEFAULT NULL, CHANGE priority_4 priority_4 VARCHAR(20) DEFAULT NULL, CHANGE priority_5 priority_5 VARCHAR(20) DEFAULT NULL, CHANGE priority_6 priority_6 VARCHAR(20) DEFAULT NULL, CHANGE password_forget_token password_forget_token VARCHAR(40) DEFAULT NULL, CHANGE layout layout VARCHAR(20) DEFAULT NULL, CHANGE palette palette VARCHAR(20) DEFAULT NULL, CHANGE access_custom_shortcuts access_custom_shortcuts LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE glpi_dashboards_dashboards (id INT AUTO_INCREMENT NOT NULL, `key` VARCHAR(100) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, name VARCHAR(100) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, context VARCHAR(100) CHARACTER SET utf8mb3 DEFAULT \'core\' NOT NULL COLLATE `utf8mb3_unicode_ci`, UNIQUE INDEX `key` (`key`), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE glpi_dashboards_items (id INT AUTO_INCREMENT NOT NULL, dashboards_dashboards_id INT NOT NULL, gridstack_id VARCHAR(100) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, card_id VARCHAR(100) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, x INT DEFAULT NULL, y INT DEFAULT NULL, width INT DEFAULT NULL, height INT DEFAULT NULL, card_options TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, INDEX dashboards_dashboards_id (dashboards_dashboards_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE glpi_dashboards_rights (id INT AUTO_INCREMENT NOT NULL, dashboards_dashboards_id INT NOT NULL, itemtype VARCHAR(100) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, items_id INT NOT NULL, INDEX dashboards_dashboards_id (dashboards_dashboards_id), UNIQUE INDEX unicity (dashboards_dashboards_id, itemtype, items_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE glpi_user_menu (name VARCHAR(255) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, user_id INT NOT NULL, content TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, PRIMARY KEY(name, user_id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE glpi_apiclients DROP FOREIGN KEY FK_D00BB2E46145D7DB');
        $this->addSql('DROP INDEX IDX_D00BB2E46145D7DB ON glpi_apiclients');
        $this->addSql('ALTER TABLE glpi_apiclients CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_appliances DROP FOREIGN KEY FK_A90A053D6145D7DB');
        $this->addSql('ALTER TABLE glpi_appliances DROP FOREIGN KEY FK_A90A053DED775E23');
        $this->addSql('ALTER TABLE glpi_appliances DROP FOREIGN KEY FK_A90A053DA2A4C2E4');
        $this->addSql('ALTER TABLE glpi_appliances DROP FOREIGN KEY FK_A90A053D72B0F067');
        $this->addSql('ALTER TABLE glpi_appliances DROP FOREIGN KEY FK_A90A053D67B3B43D');
        $this->addSql('ALTER TABLE glpi_appliances DROP FOREIGN KEY FK_A90A053DFD9C58DA');
        $this->addSql('ALTER TABLE glpi_appliances DROP FOREIGN KEY FK_A90A053DF373DCF');
        $this->addSql('ALTER TABLE glpi_appliances DROP FOREIGN KEY FK_A90A053D1421F0A5');
        $this->addSql('ALTER TABLE glpi_appliances DROP FOREIGN KEY FK_A90A053DB17973F');
        $this->addSql('ALTER TABLE glpi_appliances CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL, CHANGE locations_id locations_id INT DEFAULT 0 NOT NULL, CHANGE manufacturers_id manufacturers_id INT DEFAULT 0 NOT NULL, CHANGE applianceenvironments_id applianceenvironments_id INT DEFAULT 0 NOT NULL, CHANGE users_id users_id INT DEFAULT 0 NOT NULL, CHANGE users_id_tech users_id_tech INT DEFAULT 0 NOT NULL, CHANGE groups_id groups_id INT DEFAULT 0 NOT NULL, CHANGE groups_id_tech groups_id_tech INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_appliances_items DROP FOREIGN KEY FK_A2361859C592445B');
        $this->addSql('ALTER TABLE glpi_appliances_items CHANGE appliances_id appliances_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_appliancetypes DROP FOREIGN KEY FK_514B2A7F6145D7DB');
        $this->addSql('ALTER TABLE glpi_appliancetypes CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_authldapreplicates DROP FOREIGN KEY FK_89F2E7A47D03EC85');
        $this->addSql('ALTER TABLE glpi_authldapreplicates CHANGE authldaps_id authldaps_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_budgets DROP FOREIGN KEY FK_B6985E2C6145D7DB');
        $this->addSql('ALTER TABLE glpi_budgets DROP FOREIGN KEY FK_B6985E2CED775E23');
        $this->addSql('ALTER TABLE glpi_budgets DROP FOREIGN KEY FK_B6985E2C387998CB');
        $this->addSql('ALTER TABLE glpi_budgets CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL, CHANGE locations_id locations_id INT DEFAULT 0 NOT NULL, CHANGE budgettypes_id budgettypes_id INT DEFAULT 0 NOT NULL, CHANGE value value NUMERIC(20, 4) DEFAULT \'0.0000\' NOT NULL');
        $this->addSql('ALTER TABLE glpi_businesscriticities DROP FOREIGN KEY FK_5119F8B46145D7DB');
        $this->addSql('ALTER TABLE glpi_businesscriticities DROP FOREIGN KEY FK_5119F8B4FCE88FAB');
        $this->addSql('DROP INDEX IDX_5119F8B46145D7DB ON glpi_businesscriticities');
        $this->addSql('DROP INDEX IDX_5119F8B4FCE88FAB ON glpi_businesscriticities');
        $this->addSql('ALTER TABLE glpi_businesscriticities CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL, CHANGE businesscriticities_id businesscriticities_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_calendars DROP FOREIGN KEY FK_89F85DA66145D7DB');
        $this->addSql('DROP INDEX date_creation ON glpi_calendars');
        $this->addSql('ALTER TABLE glpi_calendars CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_calendars_holidays DROP FOREIGN KEY FK_2315C8B372C4B705');
        $this->addSql('ALTER TABLE glpi_calendars_holidays DROP FOREIGN KEY FK_2315C8B37C9675AB');
        $this->addSql('DROP INDEX IDX_2315C8B372C4B705 ON glpi_calendars_holidays');
        $this->addSql('ALTER TABLE glpi_calendars_holidays CHANGE calendars_id calendars_id INT DEFAULT 0 NOT NULL, CHANGE holidays_id holidays_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_calendarsegments DROP FOREIGN KEY FK_8021521D72C4B705');
        $this->addSql('ALTER TABLE glpi_calendarsegments DROP FOREIGN KEY FK_8021521D6145D7DB');
        $this->addSql('DROP INDEX IDX_8021521D6145D7DB ON glpi_calendarsegments');
        $this->addSql('ALTER TABLE glpi_calendarsegments CHANGE calendars_id calendars_id INT DEFAULT 0 NOT NULL, CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_cartridgeitems DROP FOREIGN KEY FK_988DAA3D6145D7DB');
        $this->addSql('ALTER TABLE glpi_cartridgeitems DROP FOREIGN KEY FK_988DAA3DED775E23');
        $this->addSql('ALTER TABLE glpi_cartridgeitems DROP FOREIGN KEY FK_988DAA3D31A4834B');
        $this->addSql('ALTER TABLE glpi_cartridgeitems DROP FOREIGN KEY FK_988DAA3DA2A4C2E4');
        $this->addSql('ALTER TABLE glpi_cartridgeitems DROP FOREIGN KEY FK_988DAA3DFD9C58DA');
        $this->addSql('ALTER TABLE glpi_cartridgeitems DROP FOREIGN KEY FK_988DAA3D1421F0A5');
        $this->addSql('ALTER TABLE glpi_cartridgeitems CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL, CHANGE locations_id locations_id INT DEFAULT 0 NOT NULL, CHANGE cartridgeitemtypes_id cartridgeitemtypes_id INT DEFAULT 0 NOT NULL, CHANGE manufacturers_id manufacturers_id INT DEFAULT 0 NOT NULL, CHANGE users_id_tech users_id_tech INT DEFAULT 0 NOT NULL, CHANGE groups_id_tech groups_id_tech INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_cartridgeitems_printermodels DROP FOREIGN KEY FK_856AD7A787A366A1');
        $this->addSql('ALTER TABLE glpi_cartridgeitems_printermodels DROP FOREIGN KEY FK_856AD7A780854B45');
        $this->addSql('DROP INDEX IDX_856AD7A780854B45 ON glpi_cartridgeitems_printermodels');
        $this->addSql('ALTER TABLE glpi_cartridgeitems_printermodels CHANGE cartridgeitems_id cartridgeitems_id INT DEFAULT 0 NOT NULL, CHANGE printermodels_id printermodels_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_cartridges DROP FOREIGN KEY FK_3185A7C76145D7DB');
        $this->addSql('ALTER TABLE glpi_cartridges DROP FOREIGN KEY FK_3185A7C787A366A1');
        $this->addSql('ALTER TABLE glpi_cartridges DROP FOREIGN KEY FK_3185A7C7713EF9E2');
        $this->addSql('ALTER TABLE glpi_cartridges CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL, CHANGE cartridgeitems_id cartridgeitems_id INT DEFAULT 0 NOT NULL, CHANGE printers_id printers_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_certificates DROP FOREIGN KEY FK_F825F1066145D7DB');
        $this->addSql('ALTER TABLE glpi_certificates DROP FOREIGN KEY FK_F825F106AF5961F5');
        $this->addSql('ALTER TABLE glpi_certificates DROP FOREIGN KEY FK_F825F106FD9C58DA');
        $this->addSql('ALTER TABLE glpi_certificates DROP FOREIGN KEY FK_F825F1061421F0A5');
        $this->addSql('ALTER TABLE glpi_certificates DROP FOREIGN KEY FK_F825F106ED775E23');
        $this->addSql('ALTER TABLE glpi_certificates DROP FOREIGN KEY FK_F825F106A2A4C2E4');
        $this->addSql('ALTER TABLE glpi_certificates DROP FOREIGN KEY FK_F825F10667B3B43D');
        $this->addSql('ALTER TABLE glpi_certificates DROP FOREIGN KEY FK_F825F106F373DCF');
        $this->addSql('ALTER TABLE glpi_certificates DROP FOREIGN KEY FK_F825F106B17973F');
        $this->addSql('ALTER TABLE glpi_certificates CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL, CHANGE certificatetypes_id certificatetypes_id INT DEFAULT 0 NOT NULL COMMENT \'RELATION to glpi_certificatetypes (id)\', CHANGE users_id_tech users_id_tech INT DEFAULT 0 NOT NULL COMMENT \'RELATION to glpi_users (id)\', CHANGE groups_id_tech groups_id_tech INT DEFAULT 0 NOT NULL COMMENT \'RELATION to glpi_groups (id)\', CHANGE locations_id locations_id INT DEFAULT 0 NOT NULL COMMENT \'RELATION to glpi_locations (id)\', CHANGE manufacturers_id manufacturers_id INT DEFAULT 0 NOT NULL COMMENT \'RELATION to glpi_manufacturers (id)\', CHANGE users_id users_id INT DEFAULT 0 NOT NULL, CHANGE groups_id groups_id INT DEFAULT 0 NOT NULL, CHANGE states_id states_id INT DEFAULT 0 NOT NULL COMMENT \'RELATION to states (id)\'');
        $this->addSql('ALTER TABLE glpi_certificates_items DROP FOREIGN KEY FK_E410E24524E411BB');
        $this->addSql('DROP INDEX IDX_E410E24524E411BB ON glpi_certificates_items');
        $this->addSql('DROP INDEX date_creation ON glpi_certificates_items');
        $this->addSql('ALTER TABLE glpi_certificates_items CHANGE certificates_id certificates_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_certificatetypes DROP FOREIGN KEY FK_CADCD7DC6145D7DB');
        $this->addSql('ALTER TABLE glpi_certificatetypes CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_changecosts DROP FOREIGN KEY FK_F846ABCA5D80B7AB');
        $this->addSql('ALTER TABLE glpi_changecosts DROP FOREIGN KEY FK_F846ABCA22FD2D3D');
        $this->addSql('ALTER TABLE glpi_changecosts DROP FOREIGN KEY FK_F846ABCA6145D7DB');
        $this->addSql('ALTER TABLE glpi_changecosts CHANGE changes_id changes_id INT DEFAULT 0 NOT NULL, CHANGE budgets_id budgets_id INT DEFAULT 0 NOT NULL, CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL, CHANGE cost_time cost_time NUMERIC(20, 4) DEFAULT \'0.0000\' NOT NULL, CHANGE cost_fixed cost_fixed NUMERIC(20, 4) DEFAULT \'0.0000\' NOT NULL, CHANGE cost_material cost_material NUMERIC(20, 4) DEFAULT \'0.0000\' NOT NULL');
        $this->addSql('ALTER TABLE glpi_changes DROP FOREIGN KEY FK_4A1273596145D7DB');
        $this->addSql('ALTER TABLE glpi_changes DROP FOREIGN KEY FK_4A127359BB756162');
        $this->addSql('ALTER TABLE glpi_changes DROP FOREIGN KEY FK_4A12735927D112BD');
        $this->addSql('ALTER TABLE glpi_changes DROP FOREIGN KEY FK_4A127359EFE9C34D');
        $this->addSql('ALTER TABLE glpi_changes CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL, CHANGE users_id_recipient users_id_recipient INT DEFAULT 0 NOT NULL, CHANGE users_id_lastupdater users_id_lastupdater INT DEFAULT 0 NOT NULL, CHANGE itilcategories_id itilcategories_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_changes_groups DROP FOREIGN KEY FK_DC2C71435D80B7AB');
        $this->addSql('ALTER TABLE glpi_changes_groups DROP FOREIGN KEY FK_DC2C7143F373DCF');
        $this->addSql('DROP INDEX IDX_DC2C71435D80B7AB ON glpi_changes_groups');
        $this->addSql('DROP INDEX IDX_DC2C7143F373DCF ON glpi_changes_groups');
        $this->addSql('ALTER TABLE glpi_changes_groups CHANGE changes_id changes_id INT DEFAULT 0 NOT NULL, CHANGE groups_id groups_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_changes_items DROP FOREIGN KEY FK_79C851F35D80B7AB');
        $this->addSql('DROP INDEX IDX_79C851F35D80B7AB ON glpi_changes_items');
        $this->addSql('ALTER TABLE glpi_changes_items CHANGE changes_id changes_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_changes_problems DROP FOREIGN KEY FK_20AE2965D80B7AB');
        $this->addSql('ALTER TABLE glpi_changes_problems DROP FOREIGN KEY FK_20AE296E30A47DD');
        $this->addSql('DROP INDEX IDX_20AE2965D80B7AB ON glpi_changes_problems');
        $this->addSql('ALTER TABLE glpi_changes_problems CHANGE changes_id changes_id INT DEFAULT 0 NOT NULL, CHANGE problems_id problems_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_changes_suppliers DROP FOREIGN KEY FK_B37E56B25D80B7AB');
        $this->addSql('ALTER TABLE glpi_changes_suppliers DROP FOREIGN KEY FK_B37E56B2355AF43');
        $this->addSql('DROP INDEX IDX_B37E56B25D80B7AB ON glpi_changes_suppliers');
        $this->addSql('DROP INDEX IDX_B37E56B2355AF43 ON glpi_changes_suppliers');
        $this->addSql('ALTER TABLE glpi_changes_suppliers CHANGE changes_id changes_id INT DEFAULT 0 NOT NULL, CHANGE suppliers_id suppliers_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_changes_tickets DROP FOREIGN KEY FK_EBBABDAA5D80B7AB');
        $this->addSql('ALTER TABLE glpi_changes_tickets DROP FOREIGN KEY FK_EBBABDAA8FDC0E9A');
        $this->addSql('DROP INDEX IDX_EBBABDAA5D80B7AB ON glpi_changes_tickets');
        $this->addSql('ALTER TABLE glpi_changes_tickets CHANGE changes_id changes_id INT DEFAULT 0 NOT NULL, CHANGE tickets_id tickets_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_changes_users DROP FOREIGN KEY FK_8C551D575D80B7AB');
        $this->addSql('ALTER TABLE glpi_changes_users DROP FOREIGN KEY FK_8C551D5767B3B43D');
        $this->addSql('DROP INDEX IDX_8C551D575D80B7AB ON glpi_changes_users');
        $this->addSql('DROP INDEX IDX_8C551D5767B3B43D ON glpi_changes_users');
        $this->addSql('ALTER TABLE glpi_changes_users CHANGE changes_id changes_id INT DEFAULT 0 NOT NULL, CHANGE users_id users_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_changetasks DROP FOREIGN KEY FK_70399F55D80B7AB');
        $this->addSql('ALTER TABLE glpi_changetasks DROP FOREIGN KEY FK_70399F530D85233');
        $this->addSql('ALTER TABLE glpi_changetasks DROP FOREIGN KEY FK_70399F567B3B43D');
        $this->addSql('ALTER TABLE glpi_changetasks DROP FOREIGN KEY FK_70399F58CBB3EB6');
        $this->addSql('ALTER TABLE glpi_changetasks DROP FOREIGN KEY FK_70399F5FD9C58DA');
        $this->addSql('ALTER TABLE glpi_changetasks DROP FOREIGN KEY FK_70399F51421F0A5');
        $this->addSql('ALTER TABLE glpi_changetasks DROP FOREIGN KEY FK_70399F53A064358');
        $this->addSql('ALTER TABLE glpi_changetasks CHANGE changes_id changes_id INT DEFAULT 0 NOT NULL, CHANGE taskcategories_id taskcategories_id INT DEFAULT 0 NOT NULL, CHANGE users_id users_id INT DEFAULT 0 NOT NULL, CHANGE users_id_editor users_id_editor INT DEFAULT 0 NOT NULL, CHANGE users_id_tech users_id_tech INT DEFAULT 0 NOT NULL, CHANGE groups_id_tech groups_id_tech INT DEFAULT 0 NOT NULL, CHANGE tasktemplates_id tasktemplates_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_changetemplatehiddenfields DROP FOREIGN KEY FK_71817FF964105530');
        $this->addSql('ALTER TABLE glpi_changetemplatehiddenfields CHANGE changetemplates_id changetemplates_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_changetemplatemandatoryfields DROP FOREIGN KEY FK_45BDDDE264105530');
        $this->addSql('ALTER TABLE glpi_changetemplatemandatoryfields CHANGE changetemplates_id changetemplates_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_changetemplatepredefinedfields DROP FOREIGN KEY FK_ECC634F464105530');
        $this->addSql('ALTER TABLE glpi_changetemplatepredefinedfields CHANGE changetemplates_id changetemplates_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_changetemplates DROP FOREIGN KEY FK_EE99887A6145D7DB');
        $this->addSql('ALTER TABLE glpi_changetemplates CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_changevalidations DROP FOREIGN KEY FK_C158C946145D7DB');
        $this->addSql('ALTER TABLE glpi_changevalidations DROP FOREIGN KEY FK_C158C9467B3B43D');
        $this->addSql('ALTER TABLE glpi_changevalidations DROP FOREIGN KEY FK_C158C945D80B7AB');
        $this->addSql('ALTER TABLE glpi_changevalidations DROP FOREIGN KEY FK_C158C94E57CE233');
        $this->addSql('ALTER TABLE glpi_changevalidations CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL, CHANGE users_id users_id INT DEFAULT 0 NOT NULL, CHANGE changes_id changes_id INT DEFAULT 0 NOT NULL, CHANGE users_id_validate users_id_validate INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_clusters DROP FOREIGN KEY FK_A63CCAB56145D7DB');
        $this->addSql('ALTER TABLE glpi_clusters DROP FOREIGN KEY FK_A63CCAB5FD9C58DA');
        $this->addSql('ALTER TABLE glpi_clusters DROP FOREIGN KEY FK_A63CCAB51421F0A5');
        $this->addSql('ALTER TABLE glpi_clusters DROP FOREIGN KEY FK_A63CCAB5B17973F');
        $this->addSql('ALTER TABLE glpi_clusters DROP FOREIGN KEY FK_A63CCAB59A4747C2');
        $this->addSql('ALTER TABLE glpi_clusters DROP FOREIGN KEY FK_A63CCAB5357A7B6F');
        $this->addSql('ALTER TABLE glpi_clusters CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL, CHANGE users_id_tech users_id_tech INT DEFAULT 0 NOT NULL, CHANGE groups_id_tech groups_id_tech INT DEFAULT 0 NOT NULL, CHANGE states_id states_id INT DEFAULT 0 NOT NULL COMMENT \'RELATION to states (id)\', CHANGE clustertypes_id clustertypes_id INT DEFAULT 0 NOT NULL, CHANGE autoupdatesystems_id autoupdatesystems_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_clustertypes DROP FOREIGN KEY FK_FAF6E9326145D7DB');
        $this->addSql('DROP INDEX date_creation ON glpi_clustertypes');
        $this->addSql('ALTER TABLE glpi_clustertypes CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_computerantiviruses DROP FOREIGN KEY FK_68671079F4B903A6');
        $this->addSql('ALTER TABLE glpi_computerantiviruses DROP FOREIGN KEY FK_68671079A2A4C2E4');
        $this->addSql('DROP INDEX IDX_68671079A2A4C2E4 ON glpi_computerantiviruses');
        $this->addSql('ALTER TABLE glpi_computerantiviruses CHANGE computers_id computers_id INT DEFAULT 0 NOT NULL, CHANGE manufacturers_id manufacturers_id INT DEFAULT 0 NOT NULL');
        $this->addSql('DROP INDEX date_creation ON glpi_computermodels');
        $this->addSql('ALTER TABLE glpi_computers DROP FOREIGN KEY FK_293E8ED86145D7DB');
        $this->addSql('ALTER TABLE glpi_computers DROP FOREIGN KEY FK_293E8ED8FD9C58DA');
        $this->addSql('ALTER TABLE glpi_computers DROP FOREIGN KEY FK_293E8ED81421F0A5');
        $this->addSql('ALTER TABLE glpi_computers DROP FOREIGN KEY FK_293E8ED8ED775E23');
        $this->addSql('ALTER TABLE glpi_computers DROP FOREIGN KEY FK_293E8ED8604D0C7C');
        $this->addSql('ALTER TABLE glpi_computers DROP FOREIGN KEY FK_293E8ED866A32204');
        $this->addSql('ALTER TABLE glpi_computers DROP FOREIGN KEY FK_293E8ED89B4E6864');
        $this->addSql('ALTER TABLE glpi_computers DROP FOREIGN KEY FK_293E8ED8A2A4C2E4');
        $this->addSql('ALTER TABLE glpi_computers DROP FOREIGN KEY FK_293E8ED867B3B43D');
        $this->addSql('ALTER TABLE glpi_computers DROP FOREIGN KEY FK_293E8ED8F373DCF');
        $this->addSql('ALTER TABLE glpi_computers DROP FOREIGN KEY FK_293E8ED8B17973F');
        $this->addSql('DROP INDEX date_creation ON glpi_computers');
        $this->addSql('ALTER TABLE glpi_computers CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL, CHANGE users_id_tech users_id_tech INT DEFAULT 0 NOT NULL, CHANGE groups_id_tech groups_id_tech INT DEFAULT 0 NOT NULL, CHANGE locations_id locations_id INT DEFAULT 0 NOT NULL, CHANGE networks_id networks_id INT DEFAULT 0 NOT NULL, CHANGE computermodels_id computermodels_id INT DEFAULT 0 NOT NULL, CHANGE computertypes_id computertypes_id INT DEFAULT 0 NOT NULL, CHANGE manufacturers_id manufacturers_id INT DEFAULT 0 NOT NULL, CHANGE users_id users_id INT DEFAULT 0 NOT NULL, CHANGE groups_id groups_id INT DEFAULT 0 NOT NULL, CHANGE states_id states_id INT DEFAULT 0 NOT NULL, CHANGE ticket_tco ticket_tco NUMERIC(20, 4) DEFAULT \'0.0000\'');
        $this->addSql('ALTER TABLE glpi_computers_items DROP FOREIGN KEY FK_BCF5679DF4B903A6');
        $this->addSql('ALTER TABLE glpi_computers_items CHANGE computers_id computers_id INT DEFAULT 0 NOT NULL');
        $this->addSql('DROP INDEX date_creation ON glpi_computertypes');
        $this->addSql('ALTER TABLE glpi_computervirtualmachines DROP FOREIGN KEY FK_6FDC320C6145D7DB');
        $this->addSql('ALTER TABLE glpi_computervirtualmachines DROP FOREIGN KEY FK_6FDC320CF4B903A6');
        $this->addSql('ALTER TABLE glpi_computervirtualmachines DROP FOREIGN KEY FK_6FDC320C9280C5B3');
        $this->addSql('ALTER TABLE glpi_computervirtualmachines DROP FOREIGN KEY FK_6FDC320CA58A2734');
        $this->addSql('ALTER TABLE glpi_computervirtualmachines DROP FOREIGN KEY FK_6FDC320C10B10554');
        $this->addSql('DROP INDEX IDX_6FDC320C10B10554 ON glpi_computervirtualmachines');
        $this->addSql('DROP INDEX date_creation ON glpi_computervirtualmachines');
        $this->addSql('ALTER TABLE glpi_computervirtualmachines CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL, CHANGE computers_id computers_id INT DEFAULT 0 NOT NULL, CHANGE virtualmachinestates_id virtualmachinestates_id INT DEFAULT 0 NOT NULL, CHANGE virtualmachinesystems_id virtualmachinesystems_id INT DEFAULT 0 NOT NULL, CHANGE virtualmachinetypes_id virtualmachinetypes_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_consumableitems DROP FOREIGN KEY FK_B83ADB4A6145D7DB');
        $this->addSql('ALTER TABLE glpi_consumableitems DROP FOREIGN KEY FK_B83ADB4AED775E23');
        $this->addSql('ALTER TABLE glpi_consumableitems DROP FOREIGN KEY FK_B83ADB4A1F067AC9');
        $this->addSql('ALTER TABLE glpi_consumableitems DROP FOREIGN KEY FK_B83ADB4AA2A4C2E4');
        $this->addSql('ALTER TABLE glpi_consumableitems DROP FOREIGN KEY FK_B83ADB4AFD9C58DA');
        $this->addSql('ALTER TABLE glpi_consumableitems DROP FOREIGN KEY FK_B83ADB4A1421F0A5');
        $this->addSql('DROP INDEX date_creation ON glpi_consumableitems');
        $this->addSql('ALTER TABLE glpi_consumableitems CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL, CHANGE locations_id locations_id INT DEFAULT 0 NOT NULL, CHANGE consumableitemtypes_id consumableitemtypes_id INT DEFAULT 0 NOT NULL, CHANGE manufacturers_id manufacturers_id INT DEFAULT 0 NOT NULL, CHANGE users_id_tech users_id_tech INT DEFAULT 0 NOT NULL, CHANGE groups_id_tech groups_id_tech INT DEFAULT 0 NOT NULL');
        $this->addSql('DROP INDEX date_creation ON glpi_consumableitemtypes');
        $this->addSql('ALTER TABLE glpi_consumables DROP FOREIGN KEY FK_F28618F26145D7DB');
        $this->addSql('ALTER TABLE glpi_consumables DROP FOREIGN KEY FK_F28618F29E324C33');
        $this->addSql('DROP INDEX date_creation ON glpi_consumables');
        $this->addSql('ALTER TABLE glpi_consumables CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL, CHANGE consumableitems_id consumableitems_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_contacts DROP FOREIGN KEY FK_79F582F96145D7DB');
        $this->addSql('ALTER TABLE glpi_contacts DROP FOREIGN KEY FK_79F582F960D5F3AB');
        $this->addSql('ALTER TABLE glpi_contacts DROP FOREIGN KEY FK_79F582F99CE64CF3');
        $this->addSql('DROP INDEX date_creation ON glpi_contacts');
        $this->addSql('ALTER TABLE glpi_contacts CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL, CHANGE contacttypes_id contacttypes_id INT DEFAULT 0 NOT NULL, CHANGE usertitles_id usertitles_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_contacts_suppliers DROP FOREIGN KEY FK_8B35180D355AF43');
        $this->addSql('ALTER TABLE glpi_contacts_suppliers DROP FOREIGN KEY FK_8B35180D719FB48E');
        $this->addSql('DROP INDEX IDX_8B35180D355AF43 ON glpi_contacts_suppliers');
        $this->addSql('ALTER TABLE glpi_contacts_suppliers CHANGE suppliers_id suppliers_id INT DEFAULT 0 NOT NULL, CHANGE contacts_id contacts_id INT DEFAULT 0 NOT NULL');
        $this->addSql('DROP INDEX date_creation ON glpi_contacttypes');
        $this->addSql('ALTER TABLE glpi_contractcosts DROP FOREIGN KEY FK_888F838124584564');
        $this->addSql('ALTER TABLE glpi_contractcosts DROP FOREIGN KEY FK_888F838122FD2D3D');
        $this->addSql('ALTER TABLE glpi_contractcosts DROP FOREIGN KEY FK_888F83816145D7DB');
        $this->addSql('ALTER TABLE glpi_contractcosts CHANGE contracts_id contracts_id INT DEFAULT 0 NOT NULL, CHANGE budgets_id budgets_id INT DEFAULT 0 NOT NULL, CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_contracts DROP FOREIGN KEY FK_47776DA6145D7DB');
        $this->addSql('ALTER TABLE glpi_contracts DROP FOREIGN KEY FK_47776DAF2ABAFE2');
        $this->addSql('ALTER TABLE glpi_contracts DROP FOREIGN KEY FK_47776DAB17973F');
        $this->addSql('DROP INDEX date_creation ON glpi_contracts');
        $this->addSql('ALTER TABLE glpi_contracts CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL, CHANGE contracttypes_id contracttypes_id INT DEFAULT 0 NOT NULL, CHANGE states_id states_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_contracts_items DROP FOREIGN KEY FK_5FF01F0E24584564');
        $this->addSql('DROP INDEX IDX_5FF01F0E24584564 ON glpi_contracts_items');
        $this->addSql('ALTER TABLE glpi_contracts_items CHANGE contracts_id contracts_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_contracts_suppliers DROP FOREIGN KEY FK_78E40104355AF43');
        $this->addSql('ALTER TABLE glpi_contracts_suppliers DROP FOREIGN KEY FK_78E4010424584564');
        $this->addSql('DROP INDEX IDX_78E40104355AF43 ON glpi_contracts_suppliers');
        $this->addSql('ALTER TABLE glpi_contracts_suppliers CHANGE suppliers_id suppliers_id INT DEFAULT 0 NOT NULL, CHANGE contracts_id contracts_id INT DEFAULT 0 NOT NULL');
        $this->addSql('DROP INDEX date_creation ON glpi_contracttypes');
        $this->addSql('ALTER TABLE glpi_crontasklogs DROP FOREIGN KEY FK_F45D647FB01F6436');
        $this->addSql('ALTER TABLE glpi_crontasklogs DROP FOREIGN KEY FK_F45D647F2D2CC539');
        $this->addSql('DROP INDEX IDX_F45D647F2D2CC539 ON glpi_crontasklogs');
        $this->addSql('ALTER TABLE glpi_crontasklogs CHANGE crontasks_id crontasks_id INT NOT NULL');
        $this->addSql('DROP INDEX date_creation ON glpi_crontasks');
        $this->addSql('ALTER TABLE glpi_dashboards DROP FOREIGN KEY FK_7331D499B26949C');
        $this->addSql('ALTER TABLE glpi_dashboards DROP FOREIGN KEY FK_7331D4964B64DCC');
        $this->addSql('DROP INDEX IDX_7331D499B26949C ON glpi_dashboards');
        $this->addSql('DROP INDEX IDX_7331D4964B64DCC ON glpi_dashboards');
        $this->addSql('ALTER TABLE glpi_dashboards CHANGE id id INT NOT NULL, CHANGE profileId profileId INT DEFAULT 0 NOT NULL, CHANGE userId userId INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_datacenters DROP FOREIGN KEY FK_D729C8696145D7DB');
        $this->addSql('ALTER TABLE glpi_datacenters DROP FOREIGN KEY FK_D729C869ED775E23');
        $this->addSql('ALTER TABLE glpi_datacenters CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL, CHANGE locations_id locations_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_dcrooms DROP FOREIGN KEY FK_BC44EC936145D7DB');
        $this->addSql('ALTER TABLE glpi_dcrooms DROP FOREIGN KEY FK_BC44EC93ED775E23');
        $this->addSql('ALTER TABLE glpi_dcrooms DROP FOREIGN KEY FK_BC44EC93EB7A8A62');
        $this->addSql('ALTER TABLE glpi_dcrooms CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL, CHANGE locations_id locations_id INT DEFAULT 0 NOT NULL, CHANGE datacenters_id datacenters_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_devicebatteries DROP FOREIGN KEY FK_A652C99DA2A4C2E4');
        $this->addSql('ALTER TABLE glpi_devicebatteries DROP FOREIGN KEY FK_A652C99D6F236F43');
        $this->addSql('ALTER TABLE glpi_devicebatteries DROP FOREIGN KEY FK_A652C99D6145D7DB');
        $this->addSql('ALTER TABLE glpi_devicebatteries DROP FOREIGN KEY FK_A652C99DC35DFA68');
        $this->addSql('DROP INDEX date_creation ON glpi_devicebatteries');
        $this->addSql('ALTER TABLE glpi_devicebatteries CHANGE manufacturers_id manufacturers_id INT DEFAULT 0 NOT NULL, CHANGE devicebatterytypes_id devicebatterytypes_id INT DEFAULT 0 NOT NULL, CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_devicecases DROP FOREIGN KEY FK_A1AE63687964C119');
        $this->addSql('ALTER TABLE glpi_devicecases DROP FOREIGN KEY FK_A1AE6368A2A4C2E4');
        $this->addSql('ALTER TABLE glpi_devicecases DROP FOREIGN KEY FK_A1AE63686145D7DB');
        $this->addSql('ALTER TABLE glpi_devicecases DROP FOREIGN KEY FK_A1AE636848F5052C');
        $this->addSql('ALTER TABLE glpi_devicecases CHANGE devicecasetypes_id devicecasetypes_id INT DEFAULT 0 NOT NULL, CHANGE manufacturers_id manufacturers_id INT DEFAULT 0 NOT NULL, CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL');
        $this->addSql('DROP INDEX date_creation ON glpi_devicecasetypes');
        $this->addSql('ALTER TABLE glpi_devicecontrols DROP FOREIGN KEY FK_8FBFCEDFA2A4C2E4');
        $this->addSql('ALTER TABLE glpi_devicecontrols DROP FOREIGN KEY FK_8FBFCEDFD08D9B0');
        $this->addSql('ALTER TABLE glpi_devicecontrols DROP FOREIGN KEY FK_8FBFCEDF6145D7DB');
        $this->addSql('ALTER TABLE glpi_devicecontrols DROP FOREIGN KEY FK_8FBFCEDFE8A65268');
        $this->addSql('ALTER TABLE glpi_devicecontrols CHANGE manufacturers_id manufacturers_id INT DEFAULT 0 NOT NULL, CHANGE interfacetypes_id interfacetypes_id INT DEFAULT 0 NOT NULL, CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_devicedrives DROP FOREIGN KEY FK_5FDF4AEDA2A4C2E4');
        $this->addSql('ALTER TABLE glpi_devicedrives DROP FOREIGN KEY FK_5FDF4AEDD08D9B0');
        $this->addSql('ALTER TABLE glpi_devicedrives DROP FOREIGN KEY FK_5FDF4AED6B59CD4B');
        $this->addSql('ALTER TABLE glpi_devicedrives CHANGE manufacturers_id manufacturers_id INT DEFAULT 0 NOT NULL, CHANGE interfacetypes_id interfacetypes_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_devicefirmwares DROP FOREIGN KEY FK_27AD954FA2A4C2E4');
        $this->addSql('ALTER TABLE glpi_devicefirmwares DROP FOREIGN KEY FK_27AD954FFE693480');
        $this->addSql('ALTER TABLE glpi_devicefirmwares DROP FOREIGN KEY FK_27AD954F6145D7DB');
        $this->addSql('ALTER TABLE glpi_devicefirmwares DROP FOREIGN KEY FK_27AD954FC1A12339');
        $this->addSql('ALTER TABLE glpi_devicefirmwares CHANGE manufacturers_id manufacturers_id INT DEFAULT 0 NOT NULL, CHANGE devicefirmwaretypes_id devicefirmwaretypes_id INT DEFAULT 0 NOT NULL, CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_devicegenerics DROP FOREIGN KEY FK_711041243D805C04');
        $this->addSql('ALTER TABLE glpi_devicegenerics DROP FOREIGN KEY FK_71104124A2A4C2E4');
        $this->addSql('ALTER TABLE glpi_devicegenerics DROP FOREIGN KEY FK_711041246145D7DB');
        $this->addSql('ALTER TABLE glpi_devicegenerics DROP FOREIGN KEY FK_71104124ED775E23');
        $this->addSql('ALTER TABLE glpi_devicegenerics DROP FOREIGN KEY FK_71104124B17973F');
        $this->addSql('ALTER TABLE glpi_devicegenerics DROP FOREIGN KEY FK_711041242BB78D68');
        $this->addSql('DROP INDEX date_creation ON glpi_devicegenerics');
        $this->addSql('ALTER TABLE glpi_devicegenerics CHANGE devicegenerictypes_id devicegenerictypes_id INT DEFAULT 0 NOT NULL, CHANGE manufacturers_id manufacturers_id INT DEFAULT 0 NOT NULL, CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL, CHANGE locations_id locations_id INT DEFAULT 0 NOT NULL, CHANGE states_id states_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_devicegraphiccards DROP FOREIGN KEY FK_13F4C69ED08D9B0');
        $this->addSql('ALTER TABLE glpi_devicegraphiccards DROP FOREIGN KEY FK_13F4C69EA2A4C2E4');
        $this->addSql('ALTER TABLE glpi_devicegraphiccards DROP FOREIGN KEY FK_13F4C69E6145D7DB');
        $this->addSql('ALTER TABLE glpi_devicegraphiccards DROP FOREIGN KEY FK_13F4C69EE40E4F0B');
        $this->addSql('DROP INDEX date_creation ON glpi_devicegraphiccards');
        $this->addSql('ALTER TABLE glpi_devicegraphiccards CHANGE interfacetypes_id interfacetypes_id INT DEFAULT 0 NOT NULL, CHANGE manufacturers_id manufacturers_id INT DEFAULT 0 NOT NULL, CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_deviceharddrives DROP FOREIGN KEY FK_EA210CE0D08D9B0');
        $this->addSql('ALTER TABLE glpi_deviceharddrives DROP FOREIGN KEY FK_EA210CE0A2A4C2E4');
        $this->addSql('ALTER TABLE glpi_deviceharddrives DROP FOREIGN KEY FK_EA210CE06145D7DB');
        $this->addSql('ALTER TABLE glpi_deviceharddrives DROP FOREIGN KEY FK_EA210CE06BF9C8E6');
        $this->addSql('ALTER TABLE glpi_deviceharddrives CHANGE interfacetypes_id interfacetypes_id INT DEFAULT 0 NOT NULL, CHANGE manufacturers_id manufacturers_id INT DEFAULT 0 NOT NULL, CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_devicememories DROP FOREIGN KEY FK_7AAE9065A2A4C2E4');
        $this->addSql('ALTER TABLE glpi_devicememories DROP FOREIGN KEY FK_7AAE9065974BFB7E');
        $this->addSql('ALTER TABLE glpi_devicememories DROP FOREIGN KEY FK_7AAE90656145D7DB');
        $this->addSql('ALTER TABLE glpi_devicememories DROP FOREIGN KEY FK_7AAE90659BCDDEED');
        $this->addSql('ALTER TABLE glpi_devicememories CHANGE manufacturers_id manufacturers_id INT DEFAULT 0 NOT NULL, CHANGE devicememorytypes_id devicememorytypes_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_devicemotherboards DROP FOREIGN KEY FK_BA4EEEB7A2A4C2E4');
        $this->addSql('ALTER TABLE glpi_devicemotherboards DROP FOREIGN KEY FK_BA4EEEB76145D7DB');
        $this->addSql('ALTER TABLE glpi_devicemotherboards DROP FOREIGN KEY FK_BA4EEEB738038C79');
        $this->addSql('ALTER TABLE glpi_devicemotherboards CHANGE manufacturers_id manufacturers_id INT DEFAULT 0 NOT NULL, CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_devicenetworkcards DROP FOREIGN KEY FK_2F394962A2A4C2E4');
        $this->addSql('ALTER TABLE glpi_devicenetworkcards DROP FOREIGN KEY FK_2F3949626145D7DB');
        $this->addSql('ALTER TABLE glpi_devicenetworkcards DROP FOREIGN KEY FK_2F394962FB338CF8');
        $this->addSql('DROP INDEX date_creation ON glpi_devicenetworkcards');
        $this->addSql('ALTER TABLE glpi_devicenetworkcards CHANGE manufacturers_id manufacturers_id INT DEFAULT 0 NOT NULL, CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_devicepcis DROP FOREIGN KEY FK_754B0561A2A4C2E4');
        $this->addSql('ALTER TABLE glpi_devicepcis DROP FOREIGN KEY FK_754B0561FB338CF8');
        $this->addSql('ALTER TABLE glpi_devicepcis DROP FOREIGN KEY FK_754B05616145D7DB');
        $this->addSql('ALTER TABLE glpi_devicepcis DROP FOREIGN KEY FK_754B0561A809D5C7');
        $this->addSql('ALTER TABLE glpi_devicepcis CHANGE manufacturers_id manufacturers_id INT DEFAULT 0 NOT NULL, CHANGE devicenetworkcardmodels_id devicenetworkcardmodels_id INT DEFAULT 0 NOT NULL, CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_devicepowersupplies DROP FOREIGN KEY FK_7C7209EDA2A4C2E4');
        $this->addSql('ALTER TABLE glpi_devicepowersupplies DROP FOREIGN KEY FK_7C7209ED6145D7DB');
        $this->addSql('ALTER TABLE glpi_devicepowersupplies DROP FOREIGN KEY FK_7C7209ED1DECE3C7');
        $this->addSql('DROP INDEX date_creation ON glpi_devicepowersupplies');
        $this->addSql('ALTER TABLE glpi_devicepowersupplies CHANGE manufacturers_id manufacturers_id INT DEFAULT 0 NOT NULL, CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_deviceprocessors DROP FOREIGN KEY FK_B6E0F8BBA2A4C2E4');
        $this->addSql('ALTER TABLE glpi_deviceprocessors DROP FOREIGN KEY FK_B6E0F8BB6145D7DB');
        $this->addSql('ALTER TABLE glpi_deviceprocessors DROP FOREIGN KEY FK_B6E0F8BBD4D3D667');
        $this->addSql('ALTER TABLE glpi_deviceprocessors CHANGE manufacturers_id manufacturers_id INT DEFAULT 0 NOT NULL, CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_devicesensors DROP FOREIGN KEY FK_E8328652B91582EB');
        $this->addSql('ALTER TABLE glpi_devicesensors DROP FOREIGN KEY FK_E83286521B86E75F');
        $this->addSql('ALTER TABLE glpi_devicesensors DROP FOREIGN KEY FK_E8328652A2A4C2E4');
        $this->addSql('ALTER TABLE glpi_devicesensors DROP FOREIGN KEY FK_E83286526145D7DB');
        $this->addSql('ALTER TABLE glpi_devicesensors DROP FOREIGN KEY FK_E8328652ED775E23');
        $this->addSql('ALTER TABLE glpi_devicesensors DROP FOREIGN KEY FK_E8328652B17973F');
        $this->addSql('DROP INDEX IDX_E83286521B86E75F ON glpi_devicesensors');
        $this->addSql('DROP INDEX date_creation ON glpi_devicesensors');
        $this->addSql('ALTER TABLE glpi_devicesensors CHANGE devicesensortypes_id devicesensortypes_id INT DEFAULT 0 NOT NULL, CHANGE devicesensormodels_id devicesensormodels_id INT DEFAULT 0 NOT NULL, CHANGE manufacturers_id manufacturers_id INT DEFAULT 0 NOT NULL, CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL, CHANGE locations_id locations_id INT DEFAULT 0 NOT NULL, CHANGE states_id states_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_devicesimcards DROP FOREIGN KEY FK_5A0BB1A86145D7DB');
        $this->addSql('ALTER TABLE glpi_devicesimcards DROP FOREIGN KEY FK_5A0BB1A8A2A4C2E4');
        $this->addSql('ALTER TABLE glpi_devicesimcards DROP FOREIGN KEY FK_5A0BB1A87A74B37');
        $this->addSql('DROP INDEX date_creation ON glpi_devicesimcards');
        $this->addSql('ALTER TABLE glpi_devicesimcards CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL, CHANGE manufacturers_id manufacturers_id INT DEFAULT 0 NOT NULL, CHANGE devicesimcardtypes_id devicesimcardtypes_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_devicesoundcards DROP FOREIGN KEY FK_D53EF47AA2A4C2E4');
        $this->addSql('ALTER TABLE glpi_devicesoundcards DROP FOREIGN KEY FK_D53EF47A6145D7DB');
        $this->addSql('ALTER TABLE glpi_devicesoundcards DROP FOREIGN KEY FK_D53EF47A53D98B78');
        $this->addSql('DROP INDEX date_creation ON glpi_devicesoundcards');
        $this->addSql('ALTER TABLE glpi_devicesoundcards CHANGE manufacturers_id manufacturers_id INT DEFAULT 0 NOT NULL, CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_displaypreferences DROP FOREIGN KEY FK_67F2BE767B3B43D');
        $this->addSql('DROP INDEX IDX_67F2BE767B3B43D ON glpi_displaypreferences');
        $this->addSql('ALTER TABLE glpi_displaypreferences CHANGE users_id users_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_documentcategories DROP FOREIGN KEY FK_44E98B1F55AC576F');
        $this->addSql('DROP INDEX IDX_44E98B1F55AC576F ON glpi_documentcategories');
        $this->addSql('DROP INDEX date_creation ON glpi_documentcategories');
        $this->addSql('ALTER TABLE glpi_documentcategories CHANGE documentcategories_id documentcategories_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_documents DROP FOREIGN KEY FK_AF97AD216145D7DB');
        $this->addSql('ALTER TABLE glpi_documents DROP FOREIGN KEY FK_AF97AD2155AC576F');
        $this->addSql('ALTER TABLE glpi_documents DROP FOREIGN KEY FK_AF97AD2167B3B43D');
        $this->addSql('ALTER TABLE glpi_documents DROP FOREIGN KEY FK_AF97AD218FDC0E9A');
        $this->addSql('ALTER TABLE glpi_documents CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL, CHANGE documentcategories_id documentcategories_id INT DEFAULT 0 NOT NULL, CHANGE users_id users_id INT DEFAULT 0 NOT NULL, CHANGE tickets_id tickets_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_documents_items DROP FOREIGN KEY FK_DDD24B255F0F2752');
        $this->addSql('ALTER TABLE glpi_documents_items DROP FOREIGN KEY FK_DDD24B256145D7DB');
        $this->addSql('ALTER TABLE glpi_documents_items DROP FOREIGN KEY FK_DDD24B2567B3B43D');
        $this->addSql('DROP INDEX IDX_DDD24B255F0F2752 ON glpi_documents_items');
        $this->addSql('DROP INDEX IDX_DDD24B256145D7DB ON glpi_documents_items');
        $this->addSql('DROP INDEX date_creation ON glpi_documents_items');
        $this->addSql('DROP INDEX date ON glpi_documents_items');
        $this->addSql('ALTER TABLE glpi_documents_items CHANGE documents_id documents_id INT DEFAULT 0 NOT NULL, CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL, CHANGE users_id users_id INT DEFAULT 0');
        $this->addSql('DROP INDEX date_creation ON glpi_documenttypes');
        $this->addSql('ALTER TABLE glpi_domainrecords DROP FOREIGN KEY FK_180F59566145D7DB');
        $this->addSql('ALTER TABLE glpi_domainrecords DROP FOREIGN KEY FK_180F59563700F4DC');
        $this->addSql('ALTER TABLE glpi_domainrecords DROP FOREIGN KEY FK_180F595671E56292');
        $this->addSql('ALTER TABLE glpi_domainrecords DROP FOREIGN KEY FK_180F5956FD9C58DA');
        $this->addSql('ALTER TABLE glpi_domainrecords DROP FOREIGN KEY FK_180F59561421F0A5');
        $this->addSql('DROP INDEX date_creation ON glpi_domainrecords');
        $this->addSql('ALTER TABLE glpi_domainrecords CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL, CHANGE domains_id domains_id INT DEFAULT 0 NOT NULL, CHANGE domainrecordtypes_id domainrecordtypes_id INT DEFAULT 0 NOT NULL, CHANGE users_id_tech users_id_tech INT DEFAULT 0 NOT NULL, CHANGE groups_id_tech groups_id_tech INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_domainrecordtypes DROP FOREIGN KEY FK_19DBFAF66145D7DB');
        $this->addSql('DROP INDEX IDX_19DBFAF66145D7DB ON glpi_domainrecordtypes');
        $this->addSql('ALTER TABLE glpi_domainrecordtypes CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_domainrelations DROP FOREIGN KEY FK_29A9192D6145D7DB');
        $this->addSql('DROP INDEX IDX_29A9192D6145D7DB ON glpi_domainrelations');
        $this->addSql('ALTER TABLE glpi_domainrelations CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_domains DROP FOREIGN KEY FK_E64974F96145D7DB');
        $this->addSql('ALTER TABLE glpi_domains DROP FOREIGN KEY FK_E64974F9182DCB32');
        $this->addSql('ALTER TABLE glpi_domains DROP FOREIGN KEY FK_E64974F9FD9C58DA');
        $this->addSql('ALTER TABLE glpi_domains DROP FOREIGN KEY FK_E64974F91421F0A5');
        $this->addSql('ALTER TABLE glpi_domains CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL, CHANGE domaintypes_id domaintypes_id INT DEFAULT 0 NOT NULL, CHANGE users_id_tech users_id_tech INT DEFAULT 0 NOT NULL, CHANGE groups_id_tech groups_id_tech INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_domains_items DROP FOREIGN KEY FK_1E9AB0AA3700F4DC');
        $this->addSql('ALTER TABLE glpi_domains_items DROP FOREIGN KEY FK_1E9AB0AA4360E635');
        $this->addSql('ALTER TABLE glpi_domains_items CHANGE domains_id domains_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_domaintypes DROP FOREIGN KEY FK_C060118E6145D7DB');
        $this->addSql('DROP INDEX IDX_C060118E6145D7DB ON glpi_domaintypes');
        $this->addSql('ALTER TABLE glpi_domaintypes CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL');
        $this->addSql('DROP INDEX date_creation ON glpi_enclosuremodels');
        $this->addSql('ALTER TABLE glpi_enclosures DROP FOREIGN KEY FK_6052344A6145D7DB');
        $this->addSql('ALTER TABLE glpi_enclosures DROP FOREIGN KEY FK_6052344AED775E23');
        $this->addSql('ALTER TABLE glpi_enclosures DROP FOREIGN KEY FK_6052344A8ED84E80');
        $this->addSql('ALTER TABLE glpi_enclosures DROP FOREIGN KEY FK_6052344AFD9C58DA');
        $this->addSql('ALTER TABLE glpi_enclosures DROP FOREIGN KEY FK_6052344A1421F0A5');
        $this->addSql('ALTER TABLE glpi_enclosures DROP FOREIGN KEY FK_6052344AB17973F');
        $this->addSql('ALTER TABLE glpi_enclosures DROP FOREIGN KEY FK_6052344AA2A4C2E4');
        $this->addSql('ALTER TABLE glpi_enclosures CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL, CHANGE locations_id locations_id INT DEFAULT 0 NOT NULL, CHANGE users_id_tech users_id_tech INT DEFAULT 0 NOT NULL, CHANGE groups_id_tech groups_id_tech INT DEFAULT 0 NOT NULL, CHANGE states_id states_id INT DEFAULT 0 NOT NULL COMMENT \'RELATION to states (id)\', CHANGE manufacturers_id manufacturers_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_entities DROP FOREIGN KEY FK_1A59F36F6145D7DB');
        $this->addSql('ALTER TABLE glpi_entities DROP FOREIGN KEY FK_1A59F36F7D03EC85');
        $this->addSql('ALTER TABLE glpi_entities DROP FOREIGN KEY FK_1A59F36F72C4B705');
        $this->addSql('ALTER TABLE glpi_entities DROP FOREIGN KEY FK_1A59F36F10E3E815');
        $this->addSql('ALTER TABLE glpi_entities DROP FOREIGN KEY FK_1A59F36F64105530');
        $this->addSql('ALTER TABLE glpi_entities DROP FOREIGN KEY FK_1A59F36F7A8D7635');
        $this->addSql('ALTER TABLE glpi_entities DROP FOREIGN KEY FK_1A59F36F16010B6F');
        $this->addSql('DROP INDEX IDX_1A59F36F7D03EC85 ON glpi_entities');
        $this->addSql('DROP INDEX IDX_1A59F36F72C4B705 ON glpi_entities');
        $this->addSql('DROP INDEX IDX_1A59F36F16010B6F ON glpi_entities');
        $this->addSql('ALTER TABLE glpi_entities CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL, CHANGE authldaps_id authldaps_id INT DEFAULT 0 NOT NULL, CHANGE calendars_id calendars_id INT DEFAULT -2 NOT NULL, CHANGE tickettemplates_id tickettemplates_id INT DEFAULT -2 NOT NULL, CHANGE changetemplates_id changetemplates_id INT DEFAULT -2 NOT NULL, CHANGE problemtemplates_id problemtemplates_id INT DEFAULT -2 NOT NULL, CHANGE entities_id_software entities_id_software INT DEFAULT -2 NOT NULL');
        $this->addSql('ALTER TABLE glpi_entities_knowbaseitems DROP FOREIGN KEY FK_30391006D89C108');
        $this->addSql('ALTER TABLE glpi_entities_knowbaseitems DROP FOREIGN KEY FK_30391006145D7DB');
        $this->addSql('ALTER TABLE glpi_entities_knowbaseitems CHANGE knowbaseitems_id knowbaseitems_id INT DEFAULT 0 NOT NULL, CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_entities_reminders DROP FOREIGN KEY FK_265E9306C7C7BF28');
        $this->addSql('ALTER TABLE glpi_entities_reminders DROP FOREIGN KEY FK_265E93066145D7DB');
        $this->addSql('ALTER TABLE glpi_entities_reminders CHANGE reminders_id reminders_id INT DEFAULT 0 NOT NULL, CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_entities_rssfeeds DROP FOREIGN KEY FK_8F946B4A2920D1F');
        $this->addSql('ALTER TABLE glpi_entities_rssfeeds DROP FOREIGN KEY FK_8F946B4A6145D7DB');
        $this->addSql('ALTER TABLE glpi_entities_rssfeeds CHANGE rssfeeds_id rssfeeds_id INT DEFAULT 0 NOT NULL, CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_fieldblacklists DROP FOREIGN KEY FK_2EF3241A6145D7DB');
        $this->addSql('DROP INDEX IDX_2EF3241A6145D7DB ON glpi_fieldblacklists');
        $this->addSql('ALTER TABLE glpi_fieldblacklists CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_fieldunicities DROP FOREIGN KEY FK_9CB981EE6145D7DB');
        $this->addSql('DROP INDEX IDX_9CB981EE6145D7DB ON glpi_fieldunicities');
        $this->addSql('ALTER TABLE glpi_fieldunicities CHANGE entities_id entities_id INT DEFAULT -1 NOT NULL');
        $this->addSql('ALTER TABLE glpi_fqdns DROP FOREIGN KEY FK_9D1D670C6145D7DB');
        $this->addSql('ALTER TABLE glpi_fqdns CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_groups DROP FOREIGN KEY FK_7286AF616145D7DB');
        $this->addSql('ALTER TABLE glpi_groups DROP FOREIGN KEY FK_7286AF61F373DCF');
        $this->addSql('ALTER TABLE glpi_groups CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL, CHANGE groups_id groups_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_groups_knowbaseitems DROP FOREIGN KEY FK_9F9797EA6D89C108');
        $this->addSql('ALTER TABLE glpi_groups_knowbaseitems DROP FOREIGN KEY FK_9F9797EAF373DCF');
        $this->addSql('ALTER TABLE glpi_groups_knowbaseitems CHANGE knowbaseitems_id knowbaseitems_id INT DEFAULT 0 NOT NULL, CHANGE groups_id groups_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_groups_problems DROP FOREIGN KEY FK_35FF34E0E30A47DD');
        $this->addSql('ALTER TABLE glpi_groups_problems DROP FOREIGN KEY FK_35FF34E0F373DCF');
        $this->addSql('DROP INDEX IDX_35FF34E0E30A47DD ON glpi_groups_problems');
        $this->addSql('DROP INDEX IDX_35FF34E0F373DCF ON glpi_groups_problems');
        $this->addSql('ALTER TABLE glpi_groups_problems CHANGE problems_id problems_id INT DEFAULT 0 NOT NULL, CHANGE groups_id groups_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_groups_reminders DROP FOREIGN KEY FK_CB9577E5C7C7BF28');
        $this->addSql('ALTER TABLE glpi_groups_reminders DROP FOREIGN KEY FK_CB9577E5F373DCF');
        $this->addSql('ALTER TABLE glpi_groups_reminders DROP FOREIGN KEY FK_CB9577E56145D7DB');
        $this->addSql('ALTER TABLE glpi_groups_reminders CHANGE reminders_id reminders_id INT DEFAULT 0 NOT NULL, CHANGE groups_id groups_id INT DEFAULT 0 NOT NULL, CHANGE entities_id entities_id INT DEFAULT -1 NOT NULL');
        $this->addSql('ALTER TABLE glpi_groups_rssfeeds DROP FOREIGN KEY FK_FCF3A8CA2920D1F');
        $this->addSql('ALTER TABLE glpi_groups_rssfeeds DROP FOREIGN KEY FK_FCF3A8CAF373DCF');
        $this->addSql('ALTER TABLE glpi_groups_rssfeeds DROP FOREIGN KEY FK_FCF3A8CA6145D7DB');
        $this->addSql('ALTER TABLE glpi_groups_rssfeeds CHANGE rssfeeds_id rssfeeds_id INT DEFAULT 0 NOT NULL, CHANGE groups_id groups_id INT DEFAULT 0 NOT NULL, CHANGE entities_id entities_id INT DEFAULT -1 NOT NULL');
        $this->addSql('ALTER TABLE glpi_groups_tickets DROP FOREIGN KEY FK_C6573B418FDC0E9A');
        $this->addSql('ALTER TABLE glpi_groups_tickets DROP FOREIGN KEY FK_C6573B41F373DCF');
        $this->addSql('DROP INDEX IDX_C6573B418FDC0E9A ON glpi_groups_tickets');
        $this->addSql('DROP INDEX IDX_C6573B41F373DCF ON glpi_groups_tickets');
        $this->addSql('ALTER TABLE glpi_groups_tickets CHANGE tickets_id tickets_id INT DEFAULT 0 NOT NULL, CHANGE groups_id groups_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_groups_users DROP FOREIGN KEY FK_3023C81467B3B43D');
        $this->addSql('ALTER TABLE glpi_groups_users DROP FOREIGN KEY FK_3023C814F373DCF');
        $this->addSql('DROP INDEX IDX_3023C81467B3B43D ON glpi_groups_users');
        $this->addSql('ALTER TABLE glpi_groups_users CHANGE users_id users_id INT DEFAULT 0 NOT NULL, CHANGE groups_id groups_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_holidays DROP FOREIGN KEY FK_70D336866145D7DB');
        $this->addSql('DROP INDEX IDX_70D336866145D7DB ON glpi_holidays');
        $this->addSql('ALTER TABLE glpi_holidays CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_infocoms DROP FOREIGN KEY FK_8D32298C6145D7DB');
        $this->addSql('ALTER TABLE glpi_infocoms DROP FOREIGN KEY FK_8D32298C355AF43');
        $this->addSql('ALTER TABLE glpi_infocoms DROP FOREIGN KEY FK_8D32298C22FD2D3D');
        $this->addSql('ALTER TABLE glpi_infocoms DROP FOREIGN KEY FK_8D32298CFCE88FAB');
        $this->addSql('ALTER TABLE glpi_infocoms CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL, CHANGE suppliers_id suppliers_id INT DEFAULT 0 NOT NULL, CHANGE budgets_id budgets_id INT DEFAULT 0 NOT NULL, CHANGE businesscriticities_id businesscriticities_id INT DEFAULT 0 NOT NULL, CHANGE value value NUMERIC(20, 4) DEFAULT \'0.0000\' NOT NULL, CHANGE warranty_value warranty_value NUMERIC(20, 4) DEFAULT \'0.0000\' NOT NULL');
        $this->addSql('ALTER TABLE glpi_ipaddresses DROP FOREIGN KEY FK_563D38B36145D7DB');
        $this->addSql('ALTER TABLE glpi_ipaddresses CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_ipaddresses_ipnetworks DROP FOREIGN KEY FK_107118A965020FC5');
        $this->addSql('ALTER TABLE glpi_ipaddresses_ipnetworks DROP FOREIGN KEY FK_107118A9A992AA50');
        $this->addSql('ALTER TABLE glpi_ipaddresses_ipnetworks CHANGE ipaddresses_id ipaddresses_id INT DEFAULT 0 NOT NULL, CHANGE ipnetworks_id ipnetworks_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_ipnetworks DROP FOREIGN KEY FK_2D47D3C86145D7DB');
        $this->addSql('ALTER TABLE glpi_ipnetworks DROP FOREIGN KEY FK_2D47D3C8A992AA50');
        $this->addSql('DROP INDEX IDX_2D47D3C86145D7DB ON glpi_ipnetworks');
        $this->addSql('DROP INDEX IDX_2D47D3C8A992AA50 ON glpi_ipnetworks');
        $this->addSql('ALTER TABLE glpi_ipnetworks CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL, CHANGE ipnetworks_id ipnetworks_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_ipnetworks_vlans DROP FOREIGN KEY FK_35A7AD8AA992AA50');
        $this->addSql('ALTER TABLE glpi_ipnetworks_vlans DROP FOREIGN KEY FK_35A7AD8A462B676C');
        $this->addSql('DROP INDEX IDX_35A7AD8AA992AA50 ON glpi_ipnetworks_vlans');
        $this->addSql('DROP INDEX IDX_35A7AD8A462B676C ON glpi_ipnetworks_vlans');
        $this->addSql('ALTER TABLE glpi_ipnetworks_vlans CHANGE ipnetworks_id ipnetworks_id INT DEFAULT 0 NOT NULL, CHANGE vlans_id vlans_id INT DEFAULT 0 NOT NULL');
        $this->addSql('DROP INDEX date_creation ON glpi_items_disks');
        $this->addSql('ALTER TABLE glpi_itilcategories DROP FOREIGN KEY FK_349D19C46145D7DB');
        $this->addSql('ALTER TABLE glpi_itilcategories DROP FOREIGN KEY FK_349D19C4EFE9C34D');
        $this->addSql('ALTER TABLE glpi_itilcategories DROP FOREIGN KEY FK_349D19C4551BC90F');
        $this->addSql('ALTER TABLE glpi_itilcategories DROP FOREIGN KEY FK_349D19C467B3B43D');
        $this->addSql('ALTER TABLE glpi_itilcategories DROP FOREIGN KEY FK_349D19C4F373DCF');
        $this->addSql('ALTER TABLE glpi_itilcategories DROP FOREIGN KEY FK_349D19C4943F6381');
        $this->addSql('ALTER TABLE glpi_itilcategories DROP FOREIGN KEY FK_349D19C44B225EE6');
        $this->addSql('ALTER TABLE glpi_itilcategories DROP FOREIGN KEY FK_349D19C464105530');
        $this->addSql('ALTER TABLE glpi_itilcategories DROP FOREIGN KEY FK_349D19C47A8D7635');
        $this->addSql('ALTER TABLE glpi_itilcategories CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL, CHANGE itilcategories_id itilcategories_id INT DEFAULT 0 NOT NULL, CHANGE knowbaseitemcategories_id knowbaseitemcategories_id INT DEFAULT 0 NOT NULL, CHANGE users_id users_id INT DEFAULT 0 NOT NULL, CHANGE groups_id groups_id INT DEFAULT 0 NOT NULL, CHANGE tickettemplates_id_incident tickettemplates_id_incident INT DEFAULT 0 NOT NULL, CHANGE tickettemplates_id_demand tickettemplates_id_demand INT DEFAULT 0 NOT NULL, CHANGE changetemplates_id changetemplates_id INT DEFAULT 0 NOT NULL, CHANGE problemtemplates_id problemtemplates_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_itilfollowups DROP FOREIGN KEY FK_1FCFA25D67B3B43D');
        $this->addSql('ALTER TABLE glpi_itilfollowups DROP FOREIGN KEY FK_1FCFA25D8CBB3EB6');
        $this->addSql('ALTER TABLE glpi_itilfollowups DROP FOREIGN KEY FK_1FCFA25DD0DEA07D');
        $this->addSql('ALTER TABLE glpi_itilfollowups CHANGE users_id users_id INT DEFAULT 0 NOT NULL, CHANGE users_id_editor users_id_editor INT DEFAULT 0 NOT NULL, CHANGE requesttypes_id requesttypes_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_itilfollowuptemplates DROP FOREIGN KEY FK_3934C13B6145D7DB');
        $this->addSql('ALTER TABLE glpi_itilfollowuptemplates DROP FOREIGN KEY FK_3934C13BD0DEA07D');
        $this->addSql('ALTER TABLE glpi_itilfollowuptemplates CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL, CHANGE requesttypes_id requesttypes_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_itils_projects DROP FOREIGN KEY FK_64C0EB2B1EDE0F55');
        $this->addSql('ALTER TABLE glpi_itils_projects CHANGE projects_id projects_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_itilsolutions DROP FOREIGN KEY FK_BF4769765E58E090');
        $this->addSql('ALTER TABLE glpi_itilsolutions DROP FOREIGN KEY FK_BF47697667B3B43D');
        $this->addSql('ALTER TABLE glpi_itilsolutions DROP FOREIGN KEY FK_BF4769768CBB3EB6');
        $this->addSql('ALTER TABLE glpi_itilsolutions DROP FOREIGN KEY FK_BF476976B18E454C');
        $this->addSql('ALTER TABLE glpi_itilsolutions DROP FOREIGN KEY FK_BF476976251F6A08');
        $this->addSql('ALTER TABLE glpi_itilsolutions CHANGE solutiontypes_id solutiontypes_id INT DEFAULT 0 NOT NULL, CHANGE users_id users_id INT DEFAULT 0 NOT NULL, CHANGE users_id_editor users_id_editor INT DEFAULT 0 NOT NULL, CHANGE users_id_approval users_id_approval INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_knowbaseitemcategories DROP FOREIGN KEY FK_60FBD1506145D7DB');
        $this->addSql('ALTER TABLE glpi_knowbaseitems DROP FOREIGN KEY FK_2E07C924551BC90F');
        $this->addSql('ALTER TABLE glpi_knowbaseitems DROP FOREIGN KEY FK_2E07C92467B3B43D');
        $this->addSql('ALTER TABLE glpi_knowbaseitems CHANGE knowbaseitemcategories_id knowbaseitemcategories_id INT DEFAULT 0 NOT NULL, CHANGE users_id users_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_knowbaseitems_comments DROP FOREIGN KEY FK_33AB06316D89C108');
        $this->addSql('ALTER TABLE glpi_knowbaseitems_comments DROP FOREIGN KEY FK_33AB063167B3B43D');
        $this->addSql('DROP INDEX IDX_33AB06316D89C108 ON glpi_knowbaseitems_comments');
        $this->addSql('DROP INDEX IDX_33AB063167B3B43D ON glpi_knowbaseitems_comments');
        $this->addSql('ALTER TABLE glpi_knowbaseitems_comments CHANGE users_id users_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_knowbaseitems_profiles DROP FOREIGN KEY FK_E705152B6D89C108');
        $this->addSql('ALTER TABLE glpi_knowbaseitems_profiles DROP FOREIGN KEY FK_E705152B22077C89');
        $this->addSql('ALTER TABLE glpi_knowbaseitems_profiles DROP FOREIGN KEY FK_E705152B6145D7DB');
        $this->addSql('ALTER TABLE glpi_knowbaseitems_profiles CHANGE knowbaseitems_id knowbaseitems_id INT DEFAULT 0 NOT NULL, CHANGE profiles_id profiles_id INT DEFAULT 0 NOT NULL, CHANGE entities_id entities_id INT DEFAULT -1 NOT NULL');
        $this->addSql('ALTER TABLE glpi_knowbaseitems_revisions DROP FOREIGN KEY FK_3B8DEF96D89C108');
        $this->addSql('DROP INDEX IDX_3B8DEF96D89C108 ON glpi_knowbaseitems_revisions');
        $this->addSql('ALTER TABLE glpi_knowbaseitems_revisions CHANGE knowbaseitems_id knowbaseitems_id INT NOT NULL');
        $this->addSql('ALTER TABLE glpi_knowbaseitems_users DROP FOREIGN KEY FK_4987D7AC6D89C108');
        $this->addSql('ALTER TABLE glpi_knowbaseitems_users DROP FOREIGN KEY FK_4987D7AC67B3B43D');
        $this->addSql('ALTER TABLE glpi_knowbaseitems_users CHANGE knowbaseitems_id knowbaseitems_id INT DEFAULT 0 NOT NULL, CHANGE users_id users_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_knowbaseitemtranslations DROP FOREIGN KEY FK_BF433A936D89C108');
        $this->addSql('ALTER TABLE glpi_knowbaseitemtranslations DROP FOREIGN KEY FK_BF433A9367B3B43D');
        $this->addSql('DROP INDEX IDX_BF433A936D89C108 ON glpi_knowbaseitemtranslations');
        $this->addSql('ALTER TABLE glpi_knowbaseitemtranslations CHANGE knowbaseitems_id knowbaseitems_id INT DEFAULT 0 NOT NULL, CHANGE users_id users_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_lineoperators DROP FOREIGN KEY FK_6E07255A6145D7DB');
        $this->addSql('ALTER TABLE glpi_lineoperators CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_lines DROP FOREIGN KEY FK_AC635CC06145D7DB');
        $this->addSql('ALTER TABLE glpi_lines DROP FOREIGN KEY FK_AC635CC067B3B43D');
        $this->addSql('ALTER TABLE glpi_lines DROP FOREIGN KEY FK_AC635CC0F373DCF');
        $this->addSql('ALTER TABLE glpi_lines DROP FOREIGN KEY FK_AC635CC0C12D38F2');
        $this->addSql('ALTER TABLE glpi_lines DROP FOREIGN KEY FK_AC635CC0ED775E23');
        $this->addSql('ALTER TABLE glpi_lines DROP FOREIGN KEY FK_AC635CC0B17973F');
        $this->addSql('ALTER TABLE glpi_lines DROP FOREIGN KEY FK_AC635CC04F5E534D');
        $this->addSql('DROP INDEX IDX_AC635CC0F373DCF ON glpi_lines');
        $this->addSql('DROP INDEX IDX_AC635CC0ED775E23 ON glpi_lines');
        $this->addSql('DROP INDEX IDX_AC635CC0B17973F ON glpi_lines');
        $this->addSql('DROP INDEX IDX_AC635CC04F5E534D ON glpi_lines');
        $this->addSql('ALTER TABLE glpi_lines CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL, CHANGE users_id users_id INT DEFAULT 0 NOT NULL, CHANGE groups_id groups_id INT DEFAULT 0 NOT NULL, CHANGE lineoperators_id lineoperators_id INT DEFAULT 0 NOT NULL, CHANGE locations_id locations_id INT DEFAULT 0 NOT NULL, CHANGE states_id states_id INT DEFAULT 0 NOT NULL, CHANGE linetypes_id linetypes_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_links DROP FOREIGN KEY FK_32E0714E6145D7DB');
        $this->addSql('DROP INDEX date_creation ON glpi_links');
        $this->addSql('ALTER TABLE glpi_links CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_locations DROP FOREIGN KEY FK_1AC195136145D7DB');
        $this->addSql('ALTER TABLE glpi_locations DROP FOREIGN KEY FK_1AC19513ED775E23');
        $this->addSql('DROP INDEX IDX_1AC195136145D7DB ON glpi_locations');
        $this->addSql('ALTER TABLE glpi_locations CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL, CHANGE locations_id locations_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_monitors DROP FOREIGN KEY FK_CC883AB76145D7DB');
        $this->addSql('ALTER TABLE glpi_monitors DROP FOREIGN KEY FK_CC883AB7FD9C58DA');
        $this->addSql('ALTER TABLE glpi_monitors DROP FOREIGN KEY FK_CC883AB71421F0A5');
        $this->addSql('ALTER TABLE glpi_monitors DROP FOREIGN KEY FK_CC883AB7ED775E23');
        $this->addSql('ALTER TABLE glpi_monitors DROP FOREIGN KEY FK_CC883AB7A996F641');
        $this->addSql('ALTER TABLE glpi_monitors DROP FOREIGN KEY FK_CC883AB72D952EDD');
        $this->addSql('ALTER TABLE glpi_monitors DROP FOREIGN KEY FK_CC883AB7A2A4C2E4');
        $this->addSql('ALTER TABLE glpi_monitors DROP FOREIGN KEY FK_CC883AB767B3B43D');
        $this->addSql('ALTER TABLE glpi_monitors DROP FOREIGN KEY FK_CC883AB7F373DCF');
        $this->addSql('ALTER TABLE glpi_monitors DROP FOREIGN KEY FK_CC883AB7B17973F');
        $this->addSql('ALTER TABLE glpi_monitors CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL, CHANGE users_id_tech users_id_tech INT DEFAULT 0 NOT NULL, CHANGE groups_id_tech groups_id_tech INT DEFAULT 0 NOT NULL, CHANGE locations_id locations_id INT DEFAULT 0 NOT NULL, CHANGE monitortypes_id monitortypes_id INT DEFAULT 0 NOT NULL, CHANGE monitormodels_id monitormodels_id INT DEFAULT 0 NOT NULL, CHANGE manufacturers_id manufacturers_id INT DEFAULT 0 NOT NULL, CHANGE users_id users_id INT DEFAULT 0 NOT NULL, CHANGE groups_id groups_id INT DEFAULT 0 NOT NULL, CHANGE states_id states_id INT DEFAULT 0 NOT NULL, CHANGE size size NUMERIC(5, 2) DEFAULT \'0.00\' NOT NULL, CHANGE ticket_tco ticket_tco NUMERIC(20, 4) DEFAULT \'0.0000\'');
        $this->addSql('ALTER TABLE glpi_netpoints DROP FOREIGN KEY FK_69DBE45C6145D7DB');
        $this->addSql('ALTER TABLE glpi_netpoints DROP FOREIGN KEY FK_69DBE45CED775E23');
        $this->addSql('DROP INDEX IDX_69DBE45C6145D7DB ON glpi_netpoints');
        $this->addSql('DROP INDEX IDX_69DBE45CED775E23 ON glpi_netpoints');
        $this->addSql('ALTER TABLE glpi_netpoints CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL, CHANGE locations_id locations_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_networkaliases DROP FOREIGN KEY FK_4F9E21DC6145D7DB');
        $this->addSql('ALTER TABLE glpi_networkaliases DROP FOREIGN KEY FK_4F9E21DC584BEB4F');
        $this->addSql('ALTER TABLE glpi_networkaliases DROP FOREIGN KEY FK_4F9E21DC6C543AFA');
        $this->addSql('DROP INDEX IDX_4F9E21DC6C543AFA ON glpi_networkaliases');
        $this->addSql('ALTER TABLE glpi_networkaliases CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL, CHANGE networknames_id networknames_id INT DEFAULT 0 NOT NULL, CHANGE fqdns_id fqdns_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_networkequipments DROP FOREIGN KEY FK_AFE59A846145D7DB');
        $this->addSql('ALTER TABLE glpi_networkequipments DROP FOREIGN KEY FK_AFE59A84FD9C58DA');
        $this->addSql('ALTER TABLE glpi_networkequipments DROP FOREIGN KEY FK_AFE59A841421F0A5');
        $this->addSql('ALTER TABLE glpi_networkequipments DROP FOREIGN KEY FK_AFE59A84ED775E23');
        $this->addSql('ALTER TABLE glpi_networkequipments DROP FOREIGN KEY FK_AFE59A84604D0C7C');
        $this->addSql('ALTER TABLE glpi_networkequipments DROP FOREIGN KEY FK_AFE59A8473C51A8B');
        $this->addSql('ALTER TABLE glpi_networkequipments DROP FOREIGN KEY FK_AFE59A8456FE569F');
        $this->addSql('ALTER TABLE glpi_networkequipments DROP FOREIGN KEY FK_AFE59A84A2A4C2E4');
        $this->addSql('ALTER TABLE glpi_networkequipments DROP FOREIGN KEY FK_AFE59A8467B3B43D');
        $this->addSql('ALTER TABLE glpi_networkequipments DROP FOREIGN KEY FK_AFE59A84F373DCF');
        $this->addSql('ALTER TABLE glpi_networkequipments DROP FOREIGN KEY FK_AFE59A84B17973F');
        $this->addSql('ALTER TABLE glpi_networkequipments CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL, CHANGE users_id_tech users_id_tech INT DEFAULT 0 NOT NULL, CHANGE groups_id_tech groups_id_tech INT DEFAULT 0 NOT NULL, CHANGE locations_id locations_id INT DEFAULT 0 NOT NULL, CHANGE networks_id networks_id INT DEFAULT 0 NOT NULL, CHANGE networkequipmenttypes_id networkequipmenttypes_id INT DEFAULT 0 NOT NULL, CHANGE networkequipmentmodels_id networkequipmentmodels_id INT DEFAULT 0 NOT NULL, CHANGE manufacturers_id manufacturers_id INT DEFAULT 0 NOT NULL, CHANGE users_id users_id INT DEFAULT 0 NOT NULL, CHANGE groups_id groups_id INT DEFAULT 0 NOT NULL, CHANGE states_id states_id INT DEFAULT 0 NOT NULL, CHANGE ticket_tco ticket_tco NUMERIC(20, 4) DEFAULT \'0.0000\'');
        $this->addSql('ALTER TABLE glpi_networknames DROP FOREIGN KEY FK_A148F0756145D7DB');
        $this->addSql('ALTER TABLE glpi_networknames DROP FOREIGN KEY FK_A148F0756C543AFA');
        $this->addSql('ALTER TABLE glpi_networknames CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL, CHANGE fqdns_id fqdns_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_networkportaggregates DROP FOREIGN KEY FK_88867CD3CE45BD77');
        $this->addSql('ALTER TABLE glpi_networkportaggregates DROP FOREIGN KEY FK_88867CD39E2F9770');
        $this->addSql('DROP INDEX IDX_88867CD39E2F9770 ON glpi_networkportaggregates');
        $this->addSql('ALTER TABLE glpi_networkportaggregates CHANGE networkports_id networkports_id INT DEFAULT 0 NOT NULL, CHANGE networkports_id_list networkports_id_list TEXT DEFAULT NULL COMMENT \'array of associated networkports_id\'');
        $this->addSql('ALTER TABLE glpi_networkportaliases DROP FOREIGN KEY FK_1ADCE793CE45BD77');
        $this->addSql('ALTER TABLE glpi_networkportaliases DROP FOREIGN KEY FK_1ADCE793A2DF6591');
        $this->addSql('ALTER TABLE glpi_networkportaliases CHANGE networkports_id networkports_id INT DEFAULT 0 NOT NULL, CHANGE networkports_id_alias networkports_id_alias INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_networkportdialups DROP FOREIGN KEY FK_E90B503DCE45BD77');
        $this->addSql('ALTER TABLE glpi_networkportdialups CHANGE networkports_id networkports_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_networkportethernets DROP FOREIGN KEY FK_9A1A7916CE45BD77');
        $this->addSql('ALTER TABLE glpi_networkportethernets DROP FOREIGN KEY FK_9A1A79165DF72560');
        $this->addSql('ALTER TABLE glpi_networkportethernets CHANGE networkports_id networkports_id INT DEFAULT 0 NOT NULL, CHANGE netpoints_id netpoints_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_networkportfiberchannels DROP FOREIGN KEY FK_C62BE585CE45BD77');
        $this->addSql('ALTER TABLE glpi_networkportfiberchannels DROP FOREIGN KEY FK_C62BE5855DF72560');
        $this->addSql('ALTER TABLE glpi_networkportfiberchannels CHANGE networkports_id networkports_id INT DEFAULT 0 NOT NULL, CHANGE netpoints_id netpoints_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_networkportlocals DROP FOREIGN KEY FK_A454ACE4CE45BD77');
        $this->addSql('ALTER TABLE glpi_networkportlocals CHANGE networkports_id networkports_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_networkports DROP FOREIGN KEY FK_DAE469F86145D7DB');
        $this->addSql('ALTER TABLE glpi_networkports CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_networkports_networkports DROP FOREIGN KEY FK_DF0512CAF88ABD1B');
        $this->addSql('ALTER TABLE glpi_networkports_networkports DROP FOREIGN KEY FK_DF0512CA6183ECA1');
        $this->addSql('DROP INDEX IDX_DF0512CAF88ABD1B ON glpi_networkports_networkports');
        $this->addSql('ALTER TABLE glpi_networkports_networkports CHANGE networkports_id_1 networkports_id_1 INT DEFAULT 0 NOT NULL, CHANGE networkports_id_2 networkports_id_2 INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_networkports_vlans DROP FOREIGN KEY FK_84FF692CCE45BD77');
        $this->addSql('ALTER TABLE glpi_networkports_vlans DROP FOREIGN KEY FK_84FF692C462B676C');
        $this->addSql('DROP INDEX IDX_84FF692CCE45BD77 ON glpi_networkports_vlans');
        $this->addSql('ALTER TABLE glpi_networkports_vlans CHANGE networkports_id networkports_id INT DEFAULT 0 NOT NULL, CHANGE vlans_id vlans_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_networkportwifis DROP FOREIGN KEY FK_FB43456ACE45BD77');
        $this->addSql('ALTER TABLE glpi_networkportwifis DROP FOREIGN KEY FK_FB43456A782248B2');
        $this->addSql('ALTER TABLE glpi_networkportwifis DROP FOREIGN KEY FK_FB43456A4D4D852B');
        $this->addSql('DROP INDEX IDX_FB43456A4D4D852B ON glpi_networkportwifis');
        $this->addSql('ALTER TABLE glpi_networkportwifis CHANGE networkports_id networkports_id INT DEFAULT 0 NOT NULL, CHANGE wifinetworks_id wifinetworks_id INT DEFAULT 0 NOT NULL, CHANGE networkportwifis_id networkportwifis_id INT DEFAULT 0 NOT NULL COMMENT \'only useful in case of Managed node\'');
        $this->addSql('ALTER TABLE glpi_notepads DROP FOREIGN KEY FK_BCDEFE2267B3B43D');
        $this->addSql('ALTER TABLE glpi_notepads DROP FOREIGN KEY FK_BCDEFE2227D112BD');
        $this->addSql('ALTER TABLE glpi_notepads CHANGE users_id users_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_notifications DROP FOREIGN KEY FK_72C25A896145D7DB');
        $this->addSql('ALTER TABLE glpi_notifications CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_notifications_notificationtemplates DROP FOREIGN KEY FK_45FE608ED4BE081');
        $this->addSql('ALTER TABLE glpi_notifications_notificationtemplates DROP FOREIGN KEY FK_45FE608EA9E8DD2B');
        $this->addSql('ALTER TABLE glpi_notifications_notificationtemplates CHANGE notifications_id notifications_id INT DEFAULT 0 NOT NULL, CHANGE notificationtemplates_id notificationtemplates_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_notificationtargets DROP FOREIGN KEY FK_9E40A2B1D4BE081');
        $this->addSql('ALTER TABLE glpi_notificationtargets CHANGE notifications_id notifications_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_notificationtemplatetranslations DROP FOREIGN KEY FK_8F8C3CD6A9E8DD2B');
        $this->addSql('ALTER TABLE glpi_notificationtemplatetranslations CHANGE notificationtemplates_id notificationtemplates_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_notimportedemails DROP FOREIGN KEY FK_36514841F9E7A2C');
        $this->addSql('ALTER TABLE glpi_notimportedemails DROP FOREIGN KEY FK_365148467B3B43D');
        $this->addSql('ALTER TABLE glpi_notimportedemails CHANGE mailcollectors_id mailcollectors_id INT DEFAULT 0 NOT NULL, CHANGE users_id users_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_objectlocks DROP FOREIGN KEY FK_55A8E45D67B3B43D');
        $this->addSql('DROP INDEX IDX_55A8E45D67B3B43D ON glpi_objectlocks');
        $this->addSql('ALTER TABLE glpi_objectlocks CHANGE users_id users_id INT NOT NULL COMMENT \'id of the locker\'');
        $this->addSql('ALTER TABLE glpi_oidc_config DROP logout');
        $this->addSql('ALTER TABLE glpi_olalevelactions DROP FOREIGN KEY FK_4ABB859EC6D702C');
        $this->addSql('ALTER TABLE glpi_olalevelactions CHANGE olalevels_id olalevels_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_olalevelcriterias DROP FOREIGN KEY FK_E04BD147C6D702C');
        $this->addSql('ALTER TABLE glpi_olalevelcriterias CHANGE olalevels_id olalevels_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_olalevels DROP FOREIGN KEY FK_EC99B26DDB7C61FE');
        $this->addSql('ALTER TABLE glpi_olalevels DROP FOREIGN KEY FK_EC99B26D6145D7DB');
        $this->addSql('DROP INDEX IDX_EC99B26D6145D7DB ON glpi_olalevels');
        $this->addSql('ALTER TABLE glpi_olalevels CHANGE olas_id olas_id INT DEFAULT 0 NOT NULL, CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_olalevels_tickets DROP FOREIGN KEY FK_B47FA3F8FDC0E9A');
        $this->addSql('ALTER TABLE glpi_olalevels_tickets DROP FOREIGN KEY FK_B47FA3FC6D702C');
        $this->addSql('ALTER TABLE glpi_olalevels_tickets CHANGE tickets_id tickets_id INT DEFAULT 0 NOT NULL, CHANGE olalevels_id olalevels_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_olas DROP FOREIGN KEY FK_B7FD34E56145D7DB');
        $this->addSql('ALTER TABLE glpi_olas DROP FOREIGN KEY FK_B7FD34E572C4B705');
        $this->addSql('ALTER TABLE glpi_olas DROP FOREIGN KEY FK_B7FD34E5BEF27A45');
        $this->addSql('DROP INDEX IDX_B7FD34E56145D7DB ON glpi_olas');
        $this->addSql('ALTER TABLE glpi_olas CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL, CHANGE calendars_id calendars_id INT DEFAULT 0 NOT NULL, CHANGE slms_id slms_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_operatingsystemkernelversions DROP FOREIGN KEY FK_69A5AEB9340E0989');
        $this->addSql('ALTER TABLE glpi_operatingsystemkernelversions CHANGE operatingsystemkernels_id operatingsystemkernels_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_passivedcequipments DROP FOREIGN KEY FK_3CF108C66145D7DB');
        $this->addSql('ALTER TABLE glpi_passivedcequipments DROP FOREIGN KEY FK_3CF108C6ED775E23');
        $this->addSql('ALTER TABLE glpi_passivedcequipments DROP FOREIGN KEY FK_3CF108C68DA5A79E');
        $this->addSql('ALTER TABLE glpi_passivedcequipments DROP FOREIGN KEY FK_3CF108C693FDCDA1');
        $this->addSql('ALTER TABLE glpi_passivedcequipments DROP FOREIGN KEY FK_3CF108C6FD9C58DA');
        $this->addSql('ALTER TABLE glpi_passivedcequipments DROP FOREIGN KEY FK_3CF108C61421F0A5');
        $this->addSql('ALTER TABLE glpi_passivedcequipments DROP FOREIGN KEY FK_3CF108C6B17973F');
        $this->addSql('ALTER TABLE glpi_passivedcequipments DROP FOREIGN KEY FK_3CF108C6A2A4C2E4');
        $this->addSql('ALTER TABLE glpi_passivedcequipments CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL, CHANGE locations_id locations_id INT DEFAULT 0 NOT NULL, CHANGE passivedcequipmenttypes_id passivedcequipmenttypes_id INT DEFAULT 0 NOT NULL, CHANGE users_id_tech users_id_tech INT DEFAULT 0 NOT NULL, CHANGE groups_id_tech groups_id_tech INT DEFAULT 0 NOT NULL, CHANGE states_id states_id INT DEFAULT 0 NOT NULL COMMENT \'RELATION to states (id)\', CHANGE manufacturers_id manufacturers_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_pdus DROP FOREIGN KEY FK_9F3AF5C16145D7DB');
        $this->addSql('ALTER TABLE glpi_pdus DROP FOREIGN KEY FK_9F3AF5C1ED775E23');
        $this->addSql('ALTER TABLE glpi_pdus DROP FOREIGN KEY FK_9F3AF5C1909D471A');
        $this->addSql('ALTER TABLE glpi_pdus DROP FOREIGN KEY FK_9F3AF5C1FD9C58DA');
        $this->addSql('ALTER TABLE glpi_pdus DROP FOREIGN KEY FK_9F3AF5C11421F0A5');
        $this->addSql('ALTER TABLE glpi_pdus DROP FOREIGN KEY FK_9F3AF5C1B17973F');
        $this->addSql('ALTER TABLE glpi_pdus DROP FOREIGN KEY FK_9F3AF5C1A2A4C2E4');
        $this->addSql('ALTER TABLE glpi_pdus DROP FOREIGN KEY FK_9F3AF5C11C0D2DB1');
        $this->addSql('ALTER TABLE glpi_pdus CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL, CHANGE locations_id locations_id INT DEFAULT 0 NOT NULL, CHANGE groups_id_tech groups_id_tech INT DEFAULT 0 NOT NULL, CHANGE states_id states_id INT DEFAULT 0 NOT NULL COMMENT \'RELATION to states (id)\', CHANGE manufacturers_id manufacturers_id INT DEFAULT 0 NOT NULL, CHANGE pdutypes_id pdutypes_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_pdus_plugs DROP FOREIGN KEY FK_460B319C15134C17');
        $this->addSql('ALTER TABLE glpi_pdus_plugs DROP FOREIGN KEY FK_460B319C33D93EF6');
        $this->addSql('ALTER TABLE glpi_pdus_plugs CHANGE plugs_id plugs_id INT DEFAULT 0 NOT NULL, CHANGE pdus_id pdus_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_pdus_racks DROP FOREIGN KEY FK_7ABF2AEF269E262D');
        $this->addSql('ALTER TABLE glpi_pdus_racks DROP FOREIGN KEY FK_7ABF2AEF33D93EF6');
        $this->addSql('ALTER TABLE glpi_pdus_racks CHANGE racks_id racks_id INT DEFAULT 0 NOT NULL, CHANGE pdus_id pdus_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_pdutypes DROP FOREIGN KEY FK_38C353DA6145D7DB');
        $this->addSql('DROP INDEX date_creation ON glpi_pdutypes');
        $this->addSql('ALTER TABLE glpi_pdutypes CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_peripherals DROP FOREIGN KEY FK_B49D1266145D7DB');
        $this->addSql('ALTER TABLE glpi_peripherals DROP FOREIGN KEY FK_B49D126FD9C58DA');
        $this->addSql('ALTER TABLE glpi_peripherals DROP FOREIGN KEY FK_B49D1261421F0A5');
        $this->addSql('ALTER TABLE glpi_peripherals DROP FOREIGN KEY FK_B49D126ED775E23');
        $this->addSql('ALTER TABLE glpi_peripherals DROP FOREIGN KEY FK_B49D12682709FED');
        $this->addSql('ALTER TABLE glpi_peripherals DROP FOREIGN KEY FK_B49D126F2DE2777');
        $this->addSql('ALTER TABLE glpi_peripherals DROP FOREIGN KEY FK_B49D126A2A4C2E4');
        $this->addSql('ALTER TABLE glpi_peripherals DROP FOREIGN KEY FK_B49D12667B3B43D');
        $this->addSql('ALTER TABLE glpi_peripherals DROP FOREIGN KEY FK_B49D126F373DCF');
        $this->addSql('ALTER TABLE glpi_peripherals DROP FOREIGN KEY FK_B49D126B17973F');
        $this->addSql('ALTER TABLE glpi_peripherals CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL, CHANGE users_id_tech users_id_tech INT DEFAULT 0 NOT NULL, CHANGE groups_id_tech groups_id_tech INT DEFAULT 0 NOT NULL, CHANGE locations_id locations_id INT DEFAULT 0 NOT NULL, CHANGE peripheraltypes_id peripheraltypes_id INT DEFAULT 0 NOT NULL, CHANGE peripheralmodels_id peripheralmodels_id INT DEFAULT 0 NOT NULL, CHANGE manufacturers_id manufacturers_id INT DEFAULT 0 NOT NULL, CHANGE users_id users_id INT DEFAULT 0 NOT NULL, CHANGE groups_id groups_id INT DEFAULT 0 NOT NULL, CHANGE states_id states_id INT DEFAULT 0 NOT NULL, CHANGE ticket_tco ticket_tco NUMERIC(20, 4) DEFAULT \'0.0000\'');
        $this->addSql('ALTER TABLE glpi_phones DROP FOREIGN KEY FK_61C3B8E4FD9C58DA');
        $this->addSql('ALTER TABLE glpi_phones DROP FOREIGN KEY FK_61C3B8E41421F0A5');
        $this->addSql('ALTER TABLE glpi_phones DROP FOREIGN KEY FK_61C3B8E4ED775E23');
        $this->addSql('ALTER TABLE glpi_phones DROP FOREIGN KEY FK_61C3B8E46AB85E18');
        $this->addSql('ALTER TABLE glpi_phones DROP FOREIGN KEY FK_61C3B8E43FE1E925');
        $this->addSql('ALTER TABLE glpi_phones DROP FOREIGN KEY FK_61C3B8E4EE911589');
        $this->addSql('ALTER TABLE glpi_phones DROP FOREIGN KEY FK_61C3B8E4A2A4C2E4');
        $this->addSql('ALTER TABLE glpi_phones DROP FOREIGN KEY FK_61C3B8E467B3B43D');
        $this->addSql('ALTER TABLE glpi_phones DROP FOREIGN KEY FK_61C3B8E4F373DCF');
        $this->addSql('ALTER TABLE glpi_phones DROP FOREIGN KEY FK_61C3B8E4B17973F');
        $this->addSql('ALTER TABLE glpi_phones CHANGE users_id_tech users_id_tech INT DEFAULT 0 NOT NULL, CHANGE groups_id_tech groups_id_tech INT DEFAULT 0 NOT NULL, CHANGE locations_id locations_id INT DEFAULT 0 NOT NULL, CHANGE phonetypes_id phonetypes_id INT DEFAULT 0 NOT NULL, CHANGE phonemodels_id phonemodels_id INT DEFAULT 0 NOT NULL, CHANGE phonepowersupplies_id phonepowersupplies_id INT DEFAULT 0 NOT NULL, CHANGE manufacturers_id manufacturers_id INT DEFAULT 0 NOT NULL, CHANGE users_id users_id INT DEFAULT 0 NOT NULL, CHANGE groups_id groups_id INT DEFAULT 0 NOT NULL, CHANGE states_id states_id INT DEFAULT 0 NOT NULL, CHANGE ticket_tco ticket_tco NUMERIC(20, 4) DEFAULT \'0.0000\'');
        $this->addSql('ALTER TABLE glpi_planningexternalevents DROP FOREIGN KEY FK_544F3E8E6145D7DB');
        $this->addSql('ALTER TABLE glpi_planningexternalevents DROP FOREIGN KEY FK_544F3E8E67B3B43D');
        $this->addSql('ALTER TABLE glpi_planningexternalevents DROP FOREIGN KEY FK_544F3E8ED5B73BE');
        $this->addSql('ALTER TABLE glpi_planningexternalevents DROP FOREIGN KEY FK_544F3E8EF373DCF');
        $this->addSql('ALTER TABLE glpi_planningexternalevents DROP FOREIGN KEY FK_544F3E8E141A4D45');
        $this->addSql('DROP INDEX IDX_544F3E8ED5B73BE ON glpi_planningexternalevents');
        $this->addSql('ALTER TABLE glpi_planningexternalevents CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL, CHANGE users_id_guests users_id_guests TEXT DEFAULT NULL, CHANGE groups_id groups_id INT DEFAULT 0 NOT NULL, CHANGE planningeventcategories_id planningeventcategories_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_planningexternaleventtemplates DROP FOREIGN KEY FK_A85DD10C6145D7DB');
        $this->addSql('ALTER TABLE glpi_planningexternaleventtemplates DROP FOREIGN KEY FK_A85DD10C141A4D45');
        $this->addSql('ALTER TABLE glpi_planningexternaleventtemplates CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL, CHANGE planningeventcategories_id planningeventcategories_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_planningrecalls DROP FOREIGN KEY FK_3BBA429167B3B43D');
        $this->addSql('ALTER TABLE glpi_planningrecalls CHANGE users_id users_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_printers DROP FOREIGN KEY FK_8F8D8A3D6145D7DB');
        $this->addSql('ALTER TABLE glpi_printers DROP FOREIGN KEY FK_8F8D8A3DFD9C58DA');
        $this->addSql('ALTER TABLE glpi_printers DROP FOREIGN KEY FK_8F8D8A3D1421F0A5');
        $this->addSql('ALTER TABLE glpi_printers DROP FOREIGN KEY FK_8F8D8A3DED775E23');
        $this->addSql('ALTER TABLE glpi_printers DROP FOREIGN KEY FK_8F8D8A3D604D0C7C');
        $this->addSql('ALTER TABLE glpi_printers DROP FOREIGN KEY FK_8F8D8A3DDE7B282B');
        $this->addSql('ALTER TABLE glpi_printers DROP FOREIGN KEY FK_8F8D8A3D80854B45');
        $this->addSql('ALTER TABLE glpi_printers DROP FOREIGN KEY FK_8F8D8A3DA2A4C2E4');
        $this->addSql('ALTER TABLE glpi_printers DROP FOREIGN KEY FK_8F8D8A3D67B3B43D');
        $this->addSql('ALTER TABLE glpi_printers DROP FOREIGN KEY FK_8F8D8A3DF373DCF');
        $this->addSql('ALTER TABLE glpi_printers DROP FOREIGN KEY FK_8F8D8A3DB17973F');
        $this->addSql('ALTER TABLE glpi_printers CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL, CHANGE users_id_tech users_id_tech INT DEFAULT 0 NOT NULL, CHANGE groups_id_tech groups_id_tech INT DEFAULT 0 NOT NULL, CHANGE locations_id locations_id INT DEFAULT 0 NOT NULL, CHANGE networks_id networks_id INT DEFAULT 0 NOT NULL, CHANGE printertypes_id printertypes_id INT DEFAULT 0 NOT NULL, CHANGE printermodels_id printermodels_id INT DEFAULT 0 NOT NULL, CHANGE manufacturers_id manufacturers_id INT DEFAULT 0 NOT NULL, CHANGE users_id users_id INT DEFAULT 0 NOT NULL, CHANGE groups_id groups_id INT DEFAULT 0 NOT NULL, CHANGE states_id states_id INT DEFAULT 0 NOT NULL, CHANGE ticket_tco ticket_tco NUMERIC(20, 4) DEFAULT \'0.0000\'');
        $this->addSql('ALTER TABLE glpi_problemcosts DROP FOREIGN KEY FK_5D343AA8E30A47DD');
        $this->addSql('ALTER TABLE glpi_problemcosts DROP FOREIGN KEY FK_5D343AA822FD2D3D');
        $this->addSql('ALTER TABLE glpi_problemcosts DROP FOREIGN KEY FK_5D343AA86145D7DB');
        $this->addSql('ALTER TABLE glpi_problemcosts CHANGE problems_id problems_id INT DEFAULT 0 NOT NULL, CHANGE budgets_id budgets_id INT DEFAULT 0 NOT NULL, CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL, CHANGE cost_time cost_time NUMERIC(20, 4) DEFAULT \'0.0000\' NOT NULL, CHANGE cost_fixed cost_fixed NUMERIC(20, 4) DEFAULT \'0.0000\' NOT NULL, CHANGE cost_material cost_material NUMERIC(20, 4) DEFAULT \'0.0000\' NOT NULL');
        $this->addSql('ALTER TABLE glpi_problems DROP FOREIGN KEY FK_C4D3F5CF6145D7DB');
        $this->addSql('ALTER TABLE glpi_problems DROP FOREIGN KEY FK_C4D3F5CFBB756162');
        $this->addSql('ALTER TABLE glpi_problems DROP FOREIGN KEY FK_C4D3F5CF27D112BD');
        $this->addSql('ALTER TABLE glpi_problems DROP FOREIGN KEY FK_C4D3F5CFEFE9C34D');
        $this->addSql('ALTER TABLE glpi_problems CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL, CHANGE users_id_recipient users_id_recipient INT DEFAULT 0 NOT NULL, CHANGE users_id_lastupdater users_id_lastupdater INT DEFAULT 0 NOT NULL, CHANGE itilcategories_id itilcategories_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_problems_suppliers DROP FOREIGN KEY FK_4A101DFE30A47DD');
        $this->addSql('ALTER TABLE glpi_problems_suppliers DROP FOREIGN KEY FK_4A101DF355AF43');
        $this->addSql('DROP INDEX IDX_4A101DFE30A47DD ON glpi_problems_suppliers');
        $this->addSql('DROP INDEX IDX_4A101DF355AF43 ON glpi_problems_suppliers');
        $this->addSql('ALTER TABLE glpi_problems_suppliers CHANGE problems_id problems_id INT DEFAULT 0 NOT NULL, CHANGE suppliers_id suppliers_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_problems_tickets DROP FOREIGN KEY FK_3DF11CF6E30A47DD');
        $this->addSql('ALTER TABLE glpi_problems_tickets DROP FOREIGN KEY FK_3DF11CF68FDC0E9A');
        $this->addSql('DROP INDEX IDX_3DF11CF6E30A47DD ON glpi_problems_tickets');
        $this->addSql('ALTER TABLE glpi_problems_tickets CHANGE problems_id problems_id INT DEFAULT 0 NOT NULL, CHANGE tickets_id tickets_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_problems_users DROP FOREIGN KEY FK_5C9612D2E30A47DD');
        $this->addSql('ALTER TABLE glpi_problems_users DROP FOREIGN KEY FK_5C9612D267B3B43D');
        $this->addSql('DROP INDEX IDX_5C9612D2E30A47DD ON glpi_problems_users');
        $this->addSql('DROP INDEX IDX_5C9612D267B3B43D ON glpi_problems_users');
        $this->addSql('ALTER TABLE glpi_problems_users CHANGE problems_id problems_id INT DEFAULT 0 NOT NULL, CHANGE users_id users_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_problemtasks DROP FOREIGN KEY FK_A2710897E30A47DD');
        $this->addSql('ALTER TABLE glpi_problemtasks DROP FOREIGN KEY FK_A271089730D85233');
        $this->addSql('ALTER TABLE glpi_problemtasks DROP FOREIGN KEY FK_A271089767B3B43D');
        $this->addSql('ALTER TABLE glpi_problemtasks DROP FOREIGN KEY FK_A27108978CBB3EB6');
        $this->addSql('ALTER TABLE glpi_problemtasks DROP FOREIGN KEY FK_A2710897FD9C58DA');
        $this->addSql('ALTER TABLE glpi_problemtasks DROP FOREIGN KEY FK_A27108971421F0A5');
        $this->addSql('ALTER TABLE glpi_problemtasks DROP FOREIGN KEY FK_A27108973A064358');
        $this->addSql('ALTER TABLE glpi_problemtasks CHANGE problems_id problems_id INT DEFAULT 0 NOT NULL, CHANGE taskcategories_id taskcategories_id INT DEFAULT 0 NOT NULL, CHANGE users_id users_id INT DEFAULT 0 NOT NULL, CHANGE users_id_editor users_id_editor INT DEFAULT 0 NOT NULL, CHANGE users_id_tech users_id_tech INT DEFAULT 0 NOT NULL, CHANGE groups_id_tech groups_id_tech INT DEFAULT 0 NOT NULL, CHANGE tasktemplates_id tasktemplates_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_problemtemplatehiddenfields DROP FOREIGN KEY FK_D90AB3B47A8D7635');
        $this->addSql('ALTER TABLE glpi_problemtemplatehiddenfields CHANGE problemtemplates_id problemtemplates_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_problemtemplatemandatoryfields DROP FOREIGN KEY FK_20DA01337A8D7635');
        $this->addSql('ALTER TABLE glpi_problemtemplatemandatoryfields CHANGE problemtemplates_id problemtemplates_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_problemtemplatepredefinedfields DROP FOREIGN KEY FK_1D77B16A7A8D7635');
        $this->addSql('ALTER TABLE glpi_problemtemplatepredefinedfields CHANGE problemtemplates_id problemtemplates_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_problemtemplates DROP FOREIGN KEY FK_38D229266145D7DB');
        $this->addSql('ALTER TABLE glpi_problemtemplates CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_profilerights DROP FOREIGN KEY FK_6E4E481722077C89');
        $this->addSql('DROP INDEX IDX_6E4E481722077C89 ON glpi_profilerights');
        $this->addSql('ALTER TABLE glpi_profilerights CHANGE profiles_id profiles_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_profiles DROP FOREIGN KEY FK_C18512BA10E3E815');
        $this->addSql('ALTER TABLE glpi_profiles DROP FOREIGN KEY FK_C18512BA64105530');
        $this->addSql('ALTER TABLE glpi_profiles DROP FOREIGN KEY FK_C18512BA7A8D7635');
        $this->addSql('ALTER TABLE glpi_profiles CHANGE tickettemplates_id tickettemplates_id INT DEFAULT 0 NOT NULL, CHANGE changetemplates_id changetemplates_id INT DEFAULT 0 NOT NULL, CHANGE problemtemplates_id problemtemplates_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_profiles_reminders DROP FOREIGN KEY FK_4A5D764FC7C7BF28');
        $this->addSql('ALTER TABLE glpi_profiles_reminders DROP FOREIGN KEY FK_4A5D764F22077C89');
        $this->addSql('ALTER TABLE glpi_profiles_reminders DROP FOREIGN KEY FK_4A5D764F6145D7DB');
        $this->addSql('ALTER TABLE glpi_profiles_reminders CHANGE reminders_id reminders_id INT DEFAULT 0 NOT NULL, CHANGE profiles_id profiles_id INT DEFAULT 0 NOT NULL, CHANGE entities_id entities_id INT DEFAULT -1 NOT NULL');
        $this->addSql('ALTER TABLE glpi_profiles_rssfeeds DROP FOREIGN KEY FK_8AE4CF1E2920D1F');
        $this->addSql('ALTER TABLE glpi_profiles_rssfeeds DROP FOREIGN KEY FK_8AE4CF1E22077C89');
        $this->addSql('ALTER TABLE glpi_profiles_rssfeeds CHANGE rssfeeds_id rssfeeds_id INT DEFAULT 0 NOT NULL, CHANGE profiles_id profiles_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_profiles_users DROP FOREIGN KEY FK_752007FA67B3B43D');
        $this->addSql('ALTER TABLE glpi_profiles_users DROP FOREIGN KEY FK_752007FA22077C89');
        $this->addSql('ALTER TABLE glpi_profiles_users DROP FOREIGN KEY FK_752007FA6145D7DB');
        $this->addSql('ALTER TABLE glpi_profiles_users CHANGE users_id users_id INT DEFAULT 0 NOT NULL, CHANGE profiles_id profiles_id INT DEFAULT 0 NOT NULL, CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_projectcosts DROP FOREIGN KEY FK_BEAAE5F21EDE0F55');
        $this->addSql('ALTER TABLE glpi_projectcosts DROP FOREIGN KEY FK_BEAAE5F222FD2D3D');
        $this->addSql('ALTER TABLE glpi_projectcosts DROP FOREIGN KEY FK_BEAAE5F26145D7DB');
        $this->addSql('ALTER TABLE glpi_projectcosts CHANGE projects_id projects_id INT DEFAULT 0 NOT NULL, CHANGE budgets_id budgets_id INT DEFAULT 0 NOT NULL, CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL, CHANGE cost cost NUMERIC(20, 4) DEFAULT \'0.0000\' NOT NULL');
        $this->addSql('ALTER TABLE glpi_projects DROP FOREIGN KEY FK_1626242E6145D7DB');
        $this->addSql('ALTER TABLE glpi_projects DROP FOREIGN KEY FK_1626242E1EDE0F55');
        $this->addSql('ALTER TABLE glpi_projects DROP FOREIGN KEY FK_1626242E18995984');
        $this->addSql('ALTER TABLE glpi_projects DROP FOREIGN KEY FK_1626242E6CE4DE4F');
        $this->addSql('ALTER TABLE glpi_projects DROP FOREIGN KEY FK_1626242E67B3B43D');
        $this->addSql('ALTER TABLE glpi_projects DROP FOREIGN KEY FK_1626242EF373DCF');
        $this->addSql('DROP INDEX date_creation ON glpi_projects');
        $this->addSql('ALTER TABLE glpi_projects CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL, CHANGE projects_id projects_id INT DEFAULT 0 NOT NULL, CHANGE projectstates_id projectstates_id INT DEFAULT 0 NOT NULL, CHANGE projecttypes_id projecttypes_id INT DEFAULT 0 NOT NULL, CHANGE users_id users_id INT DEFAULT 0 NOT NULL, CHANGE groups_id groups_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_projecttasks DROP FOREIGN KEY FK_41EFD7CD6145D7DB');
        $this->addSql('ALTER TABLE glpi_projecttasks DROP FOREIGN KEY FK_41EFD7CD1EDE0F55');
        $this->addSql('ALTER TABLE glpi_projecttasks DROP FOREIGN KEY FK_41EFD7CD171C029');
        $this->addSql('ALTER TABLE glpi_projecttasks DROP FOREIGN KEY FK_41EFD7CD18995984');
        $this->addSql('ALTER TABLE glpi_projecttasks DROP FOREIGN KEY FK_41EFD7CD7369BDC5');
        $this->addSql('ALTER TABLE glpi_projecttasks DROP FOREIGN KEY FK_41EFD7CD67B3B43D');
        $this->addSql('ALTER TABLE glpi_projecttasks DROP FOREIGN KEY FK_41EFD7CD7FECD144');
        $this->addSql('ALTER TABLE glpi_projecttasks CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL, CHANGE projects_id projects_id INT DEFAULT 0 NOT NULL, CHANGE projecttasks_id projecttasks_id INT DEFAULT 0 NOT NULL, CHANGE projectstates_id projectstates_id INT DEFAULT 0 NOT NULL, CHANGE projecttasktypes_id projecttasktypes_id INT DEFAULT 0 NOT NULL, CHANGE users_id users_id INT DEFAULT 0 NOT NULL, CHANGE projecttasktemplates_id projecttasktemplates_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_projecttasks_tickets DROP FOREIGN KEY FK_2D48CB0A8FDC0E9A');
        $this->addSql('ALTER TABLE glpi_projecttasks_tickets DROP FOREIGN KEY FK_2D48CB0A171C029');
        $this->addSql('DROP INDEX IDX_2D48CB0A8FDC0E9A ON glpi_projecttasks_tickets');
        $this->addSql('ALTER TABLE glpi_projecttasks_tickets CHANGE tickets_id tickets_id INT DEFAULT 0 NOT NULL, CHANGE projecttasks_id projecttasks_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_projecttaskteams DROP FOREIGN KEY FK_1B0A1B0D171C029');
        $this->addSql('DROP INDEX IDX_1B0A1B0D171C029 ON glpi_projecttaskteams');
        $this->addSql('ALTER TABLE glpi_projecttaskteams CHANGE projecttasks_id projecttasks_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_projecttasktemplates DROP FOREIGN KEY FK_286BFEDA6145D7DB');
        $this->addSql('ALTER TABLE glpi_projecttasktemplates DROP FOREIGN KEY FK_286BFEDA1EDE0F55');
        $this->addSql('ALTER TABLE glpi_projecttasktemplates DROP FOREIGN KEY FK_286BFEDA171C029');
        $this->addSql('ALTER TABLE glpi_projecttasktemplates DROP FOREIGN KEY FK_286BFEDA18995984');
        $this->addSql('ALTER TABLE glpi_projecttasktemplates DROP FOREIGN KEY FK_286BFEDA7369BDC5');
        $this->addSql('ALTER TABLE glpi_projecttasktemplates DROP FOREIGN KEY FK_286BFEDA67B3B43D');
        $this->addSql('ALTER TABLE glpi_projecttasktemplates CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL, CHANGE projects_id projects_id INT DEFAULT 0 NOT NULL, CHANGE projecttasks_id projecttasks_id INT DEFAULT 0 NOT NULL, CHANGE projectstates_id projectstates_id INT DEFAULT 0 NOT NULL, CHANGE projecttasktypes_id projecttasktypes_id INT DEFAULT 0 NOT NULL, CHANGE users_id users_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_projectteams DROP FOREIGN KEY FK_877590021EDE0F55');
        $this->addSql('DROP INDEX IDX_877590021EDE0F55 ON glpi_projectteams');
        $this->addSql('ALTER TABLE glpi_projectteams CHANGE projects_id projects_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_queuedchats DROP FOREIGN KEY FK_7E072DC2A9E8DD2B');
        $this->addSql('ALTER TABLE glpi_queuedchats DROP FOREIGN KEY FK_7E072DC26145D7DB');
        $this->addSql('ALTER TABLE glpi_queuedchats DROP FOREIGN KEY FK_7E072DC2ED775E23');
        $this->addSql('ALTER TABLE glpi_queuedchats DROP FOREIGN KEY FK_7E072DC2F373DCF');
        $this->addSql('ALTER TABLE glpi_queuedchats DROP FOREIGN KEY FK_7E072DC2EFE9C34D');
        $this->addSql('DROP INDEX IDX_7E072DC2A9E8DD2B ON glpi_queuedchats');
        $this->addSql('DROP INDEX IDX_7E072DC2ED775E23 ON glpi_queuedchats');
        $this->addSql('DROP INDEX IDX_7E072DC2F373DCF ON glpi_queuedchats');
        $this->addSql('DROP INDEX IDX_7E072DC2EFE9C34D ON glpi_queuedchats');
        $this->addSql('ALTER TABLE glpi_queuedchats CHANGE notificationtemplates_id notificationtemplates_id INT DEFAULT 0 NOT NULL, CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL, CHANGE locations_id locations_id INT DEFAULT 0 NOT NULL, CHANGE groups_id groups_id INT DEFAULT 0 NOT NULL, CHANGE itilcategories_id itilcategories_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_queuednotifications DROP FOREIGN KEY FK_FDE96054A9E8DD2B');
        $this->addSql('ALTER TABLE glpi_queuednotifications DROP FOREIGN KEY FK_FDE960546145D7DB');
        $this->addSql('DROP INDEX IDX_FDE96054A9E8DD2B ON glpi_queuednotifications');
        $this->addSql('ALTER TABLE glpi_queuednotifications CHANGE notificationtemplates_id notificationtemplates_id INT DEFAULT 0 NOT NULL, CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_racks DROP FOREIGN KEY FK_205CE3116145D7DB');
        $this->addSql('ALTER TABLE glpi_racks DROP FOREIGN KEY FK_205CE311ED775E23');
        $this->addSql('ALTER TABLE glpi_racks DROP FOREIGN KEY FK_205CE311B9750B73');
        $this->addSql('ALTER TABLE glpi_racks DROP FOREIGN KEY FK_205CE311A2A4C2E4');
        $this->addSql('ALTER TABLE glpi_racks DROP FOREIGN KEY FK_205CE3112D88DCC8');
        $this->addSql('ALTER TABLE glpi_racks DROP FOREIGN KEY FK_205CE311B17973F');
        $this->addSql('ALTER TABLE glpi_racks DROP FOREIGN KEY FK_205CE311FD9C58DA');
        $this->addSql('ALTER TABLE glpi_racks DROP FOREIGN KEY FK_205CE3111421F0A5');
        $this->addSql('ALTER TABLE glpi_racks DROP FOREIGN KEY FK_205CE311B569C6DF');
        $this->addSql('ALTER TABLE glpi_racks CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL, CHANGE locations_id locations_id INT DEFAULT 0 NOT NULL, CHANGE manufacturers_id manufacturers_id INT DEFAULT 0 NOT NULL, CHANGE racktypes_id racktypes_id INT DEFAULT 0 NOT NULL, CHANGE states_id states_id INT DEFAULT 0 NOT NULL, CHANGE users_id_tech users_id_tech INT DEFAULT 0 NOT NULL, CHANGE groups_id_tech groups_id_tech INT DEFAULT 0 NOT NULL, CHANGE dcrooms_id dcrooms_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_racktypes DROP FOREIGN KEY FK_6E4557BD6145D7DB');
        $this->addSql('ALTER TABLE glpi_racktypes CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_reminders DROP FOREIGN KEY FK_60B5667D67B3B43D');
        $this->addSql('ALTER TABLE glpi_reminders CHANGE users_id users_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_reminders_users DROP FOREIGN KEY FK_5D0EA00FC7C7BF28');
        $this->addSql('ALTER TABLE glpi_reminders_users DROP FOREIGN KEY FK_5D0EA00F67B3B43D');
        $this->addSql('ALTER TABLE glpi_reminders_users CHANGE reminders_id reminders_id INT DEFAULT 0 NOT NULL, CHANGE users_id users_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_remindertranslations DROP FOREIGN KEY FK_BE66B0AAC7C7BF28');
        $this->addSql('ALTER TABLE glpi_remindertranslations DROP FOREIGN KEY FK_BE66B0AA67B3B43D');
        $this->addSql('DROP INDEX IDX_BE66B0AAC7C7BF28 ON glpi_remindertranslations');
        $this->addSql('ALTER TABLE glpi_remindertranslations CHANGE reminders_id reminders_id INT DEFAULT 0 NOT NULL, CHANGE users_id users_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_reservationitems DROP FOREIGN KEY FK_1AD247B06145D7DB');
        $this->addSql('ALTER TABLE glpi_reservationitems CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_reservations DROP FOREIGN KEY FK_754EA860786DF47C');
        $this->addSql('ALTER TABLE glpi_reservations DROP FOREIGN KEY FK_754EA86067B3B43D');
        $this->addSql('ALTER TABLE glpi_reservations CHANGE reservationitems_id reservationitems_id INT DEFAULT 0 NOT NULL, CHANGE users_id users_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_rssfeeds DROP FOREIGN KEY FK_DDF69E567B3B43D');
        $this->addSql('ALTER TABLE glpi_rssfeeds CHANGE users_id users_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_rssfeeds_users DROP FOREIGN KEY FK_3AFFECE42920D1F');
        $this->addSql('ALTER TABLE glpi_rssfeeds_users DROP FOREIGN KEY FK_3AFFECE467B3B43D');
        $this->addSql('ALTER TABLE glpi_rssfeeds_users CHANGE rssfeeds_id rssfeeds_id INT DEFAULT 0 NOT NULL, CHANGE users_id users_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_ruleactions DROP FOREIGN KEY FK_E78233EFB699244');
        $this->addSql('ALTER TABLE glpi_ruleactions CHANGE rules_id rules_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_rulecriterias DROP FOREIGN KEY FK_71F92FB0FB699244');
        $this->addSql('ALTER TABLE glpi_rulecriterias CHANGE rules_id rules_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_rules DROP FOREIGN KEY FK_6AF8496A6145D7DB');
        $this->addSql('ALTER TABLE glpi_rules CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_savedsearches DROP FOREIGN KEY FK_8C93FCA967B3B43D');
        $this->addSql('ALTER TABLE glpi_savedsearches DROP FOREIGN KEY FK_8C93FCA96145D7DB');
        $this->addSql('ALTER TABLE glpi_savedsearches CHANGE users_id users_id INT DEFAULT 0 NOT NULL, CHANGE entities_id entities_id INT DEFAULT -1 NOT NULL');
        $this->addSql('ALTER TABLE glpi_savedsearches_alerts DROP FOREIGN KEY FK_8F033C74D137DC92');
        $this->addSql('DROP INDEX IDX_8F033C74D137DC92 ON glpi_savedsearches_alerts');
        $this->addSql('ALTER TABLE glpi_savedsearches_alerts CHANGE savedsearches_id savedsearches_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_savedsearches_users DROP FOREIGN KEY FK_6AB618A167B3B43D');
        $this->addSql('ALTER TABLE glpi_savedsearches_users DROP FOREIGN KEY FK_6AB618A1D137DC92');
        $this->addSql('DROP INDEX IDX_6AB618A167B3B43D ON glpi_savedsearches_users');
        $this->addSql('ALTER TABLE glpi_savedsearches_users CHANGE users_id users_id INT DEFAULT 0 NOT NULL, CHANGE savedsearches_id savedsearches_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_slalevelactions DROP FOREIGN KEY FK_4B2CB33557FD051');
        $this->addSql('ALTER TABLE glpi_slalevelactions CHANGE slalevels_id slalevels_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_slalevelcriterias DROP FOREIGN KEY FK_6202206B57FD051');
        $this->addSql('ALTER TABLE glpi_slalevelcriterias CHANGE slalevels_id slalevels_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_slalevels DROP FOREIGN KEY FK_A66D03087B029744');
        $this->addSql('ALTER TABLE glpi_slalevels DROP FOREIGN KEY FK_A66D03086145D7DB');
        $this->addSql('DROP INDEX IDX_A66D03086145D7DB ON glpi_slalevels');
        $this->addSql('ALTER TABLE glpi_slalevels CHANGE slas_id slas_id INT DEFAULT 0 NOT NULL, CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_slalevels_tickets DROP FOREIGN KEY FK_890E0B138FDC0E9A');
        $this->addSql('ALTER TABLE glpi_slalevels_tickets DROP FOREIGN KEY FK_890E0B1357FD051');
        $this->addSql('ALTER TABLE glpi_slalevels_tickets CHANGE tickets_id tickets_id INT DEFAULT 0 NOT NULL, CHANGE slalevels_id slalevels_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_slas DROP FOREIGN KEY FK_AD32DCC26145D7DB');
        $this->addSql('ALTER TABLE glpi_slas DROP FOREIGN KEY FK_AD32DCC272C4B705');
        $this->addSql('ALTER TABLE glpi_slas DROP FOREIGN KEY FK_AD32DCC2BEF27A45');
        $this->addSql('DROP INDEX IDX_AD32DCC26145D7DB ON glpi_slas');
        $this->addSql('ALTER TABLE glpi_slas CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL, CHANGE calendars_id calendars_id INT DEFAULT 0 NOT NULL, CHANGE slms_id slms_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_slms DROP FOREIGN KEY FK_18793CE6145D7DB');
        $this->addSql('ALTER TABLE glpi_slms DROP FOREIGN KEY FK_18793CE72C4B705');
        $this->addSql('ALTER TABLE glpi_slms CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL, CHANGE calendars_id calendars_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_softwarecategories DROP FOREIGN KEY FK_5A90EC8AAD111992');
        $this->addSql('ALTER TABLE glpi_softwarecategories CHANGE softwarecategories_id softwarecategories_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_softwarelicenses DROP FOREIGN KEY FK_8DF16B58E67D8904');
        $this->addSql('ALTER TABLE glpi_softwarelicenses DROP FOREIGN KEY FK_8DF16B5844CA6F2F');
        $this->addSql('ALTER TABLE glpi_softwarelicenses DROP FOREIGN KEY FK_8DF16B586145D7DB');
        $this->addSql('ALTER TABLE glpi_softwarelicenses DROP FOREIGN KEY FK_8DF16B5885A13A28');
        $this->addSql('ALTER TABLE glpi_softwarelicenses DROP FOREIGN KEY FK_8DF16B586C46BCBA');
        $this->addSql('ALTER TABLE glpi_softwarelicenses DROP FOREIGN KEY FK_8DF16B583774F286');
        $this->addSql('ALTER TABLE glpi_softwarelicenses DROP FOREIGN KEY FK_8DF16B58ED775E23');
        $this->addSql('ALTER TABLE glpi_softwarelicenses DROP FOREIGN KEY FK_8DF16B58FD9C58DA');
        $this->addSql('ALTER TABLE glpi_softwarelicenses DROP FOREIGN KEY FK_8DF16B5867B3B43D');
        $this->addSql('ALTER TABLE glpi_softwarelicenses DROP FOREIGN KEY FK_8DF16B581421F0A5');
        $this->addSql('ALTER TABLE glpi_softwarelicenses DROP FOREIGN KEY FK_8DF16B58B17973F');
        $this->addSql('ALTER TABLE glpi_softwarelicenses DROP FOREIGN KEY FK_8DF16B58A2A4C2E4');
        $this->addSql('DROP INDEX IDX_8DF16B58E67D8904 ON glpi_softwarelicenses');
        $this->addSql('DROP INDEX IDX_8DF16B5844CA6F2F ON glpi_softwarelicenses');
        $this->addSql('ALTER TABLE glpi_softwarelicenses CHANGE softwares_id softwares_id INT DEFAULT 0 NOT NULL, CHANGE softwarelicenses_id softwarelicenses_id INT DEFAULT 0 NOT NULL, CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL, CHANGE softwarelicensetypes_id softwarelicensetypes_id INT DEFAULT 0 NOT NULL, CHANGE softwareversions_id_buy softwareversions_id_buy INT DEFAULT 0 NOT NULL, CHANGE locations_id locations_id INT DEFAULT 0 NOT NULL, CHANGE users_id_tech users_id_tech INT DEFAULT 0 NOT NULL, CHANGE groups_id_tech groups_id_tech INT DEFAULT 0 NOT NULL, CHANGE states_id states_id INT DEFAULT 0 NOT NULL, CHANGE manufacturers_id manufacturers_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_softwarelicensetypes DROP FOREIGN KEY FK_D4B117C385A13A28');
        $this->addSql('ALTER TABLE glpi_softwarelicensetypes DROP FOREIGN KEY FK_D4B117C36145D7DB');
        $this->addSql('DROP INDEX IDX_D4B117C36145D7DB ON glpi_softwarelicensetypes');
        $this->addSql('ALTER TABLE glpi_softwarelicensetypes CHANGE softwarelicensetypes_id softwarelicensetypes_id INT DEFAULT 0 NOT NULL, CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_softwares DROP FOREIGN KEY FK_1D851FEB6145D7DB');
        $this->addSql('ALTER TABLE glpi_softwares DROP FOREIGN KEY FK_1D851FEBED775E23');
        $this->addSql('ALTER TABLE glpi_softwares DROP FOREIGN KEY FK_1D851FEBFD9C58DA');
        $this->addSql('ALTER TABLE glpi_softwares DROP FOREIGN KEY FK_1D851FEB1421F0A5');
        $this->addSql('ALTER TABLE glpi_softwares DROP FOREIGN KEY FK_1D851FEBE67D8904');
        $this->addSql('ALTER TABLE glpi_softwares DROP FOREIGN KEY FK_1D851FEBA2A4C2E4');
        $this->addSql('ALTER TABLE glpi_softwares DROP FOREIGN KEY FK_1D851FEB67B3B43D');
        $this->addSql('ALTER TABLE glpi_softwares DROP FOREIGN KEY FK_1D851FEBF373DCF');
        $this->addSql('ALTER TABLE glpi_softwares CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL, CHANGE locations_id locations_id INT DEFAULT 0 NOT NULL, CHANGE users_id_tech users_id_tech INT DEFAULT 0 NOT NULL, CHANGE groups_id_tech groups_id_tech INT DEFAULT 0 NOT NULL, CHANGE softwares_id softwares_id INT DEFAULT 0 NOT NULL, CHANGE manufacturers_id manufacturers_id INT DEFAULT 0 NOT NULL, CHANGE users_id users_id INT DEFAULT 0 NOT NULL, CHANGE groups_id groups_id INT DEFAULT 0 NOT NULL, CHANGE ticket_tco ticket_tco NUMERIC(20, 4) DEFAULT \'0.0000\'');
        $this->addSql('ALTER TABLE glpi_softwareversions DROP FOREIGN KEY FK_EB1F24B56145D7DB');
        $this->addSql('ALTER TABLE glpi_softwareversions DROP FOREIGN KEY FK_EB1F24B5E67D8904');
        $this->addSql('ALTER TABLE glpi_softwareversions DROP FOREIGN KEY FK_EB1F24B57F852578');
        $this->addSql('ALTER TABLE glpi_softwareversions CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL, CHANGE softwares_id softwares_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_solutiontemplates DROP FOREIGN KEY FK_6048BE7A6145D7DB');
        $this->addSql('ALTER TABLE glpi_solutiontemplates DROP FOREIGN KEY FK_6048BE7A5E58E090');
        $this->addSql('ALTER TABLE glpi_solutiontemplates CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL, CHANGE solutiontypes_id solutiontypes_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_solutiontypes DROP FOREIGN KEY FK_B819008A6145D7DB');
        $this->addSql('ALTER TABLE glpi_solutiontypes CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_states DROP FOREIGN KEY FK_B329E15C6145D7DB');
        $this->addSql('ALTER TABLE glpi_states DROP FOREIGN KEY FK_B329E15CB17973F');
        $this->addSql('DROP INDEX IDX_B329E15C6145D7DB ON glpi_states');
        $this->addSql('DROP INDEX IDX_B329E15CB17973F ON glpi_states');
        $this->addSql('ALTER TABLE glpi_states CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL, CHANGE states_id states_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_suppliers DROP FOREIGN KEY FK_A10F66F56145D7DB');
        $this->addSql('ALTER TABLE glpi_suppliers DROP FOREIGN KEY FK_A10F66F57B9FA635');
        $this->addSql('ALTER TABLE glpi_suppliers CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL, CHANGE suppliertypes_id suppliertypes_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_suppliers_tickets DROP FOREIGN KEY FK_C3F21B8B8FDC0E9A');
        $this->addSql('ALTER TABLE glpi_suppliers_tickets DROP FOREIGN KEY FK_C3F21B8B355AF43');
        $this->addSql('DROP INDEX IDX_C3F21B8B8FDC0E9A ON glpi_suppliers_tickets');
        $this->addSql('DROP INDEX IDX_C3F21B8B355AF43 ON glpi_suppliers_tickets');
        $this->addSql('ALTER TABLE glpi_suppliers_tickets CHANGE tickets_id tickets_id INT DEFAULT 0 NOT NULL, CHANGE suppliers_id suppliers_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_taskcategories DROP FOREIGN KEY FK_83E950246145D7DB');
        $this->addSql('ALTER TABLE glpi_taskcategories DROP FOREIGN KEY FK_83E9502430D85233');
        $this->addSql('ALTER TABLE glpi_taskcategories DROP FOREIGN KEY FK_83E95024551BC90F');
        $this->addSql('ALTER TABLE glpi_taskcategories CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL, CHANGE taskcategories_id taskcategories_id INT DEFAULT 0 NOT NULL, CHANGE knowbaseitemcategories_id knowbaseitemcategories_id INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_tasktemplates DROP FOREIGN KEY FK_13EA38F86145D7DB');
        $this->addSql('ALTER TABLE glpi_tasktemplates DROP FOREIGN KEY FK_13EA38F830D85233');
        $this->addSql('ALTER TABLE glpi_tasktemplates DROP FOREIGN KEY FK_13EA38F8FD9C58DA');
        $this->addSql('ALTER TABLE glpi_tasktemplates DROP FOREIGN KEY FK_13EA38F81421F0A5');
        $this->addSql('ALTER TABLE glpi_tasktemplates CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL, CHANGE taskcategories_id taskcategories_id INT DEFAULT 0 NOT NULL, CHANGE users_id_tech users_id_tech INT DEFAULT 0 NOT NULL, CHANGE groups_id_tech groups_id_tech INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_ticketcosts DROP FOREIGN KEY FK_A94AF7498FDC0E9A');
        $this->addSql('ALTER TABLE glpi_ticketcosts DROP FOREIGN KEY FK_A94AF74922FD2D3D');
        $this->addSql('ALTER TABLE glpi_ticketcosts DROP FOREIGN KEY FK_A94AF7496145D7DB');
        $this->addSql('ALTER TABLE glpi_ticketcosts CHANGE tickets_id tickets_id INT DEFAULT 0 NOT NULL, CHANGE budgets_id budgets_id INT DEFAULT 0 NOT NULL, CHANGE entities_id entities_id INT DEFAULT 0 NOT NULL, CHANGE cost_time cost_time NUMERIC(20, 4) DEFAULT \'0.0000\' NOT NULL, CHANGE cost_fixed cost_fixed NUMERIC(20, 4) DEFAULT \'0.0000\' NOT NULL, CHANGE cost_material cost_material NUMERIC(20, 4) DEFAULT \'0.0000\' NOT NULL');
        $this->addSql('ALTER TABLE glpi_users CHANGE language language CHAR(10) DEFAULT NULL COMMENT \'see define.php CFG_GLPI[language] array\', CHANGE csv_delimiter csv_delimiter CHAR(1) DEFAULT NULL, CHANGE priority_1 priority_1 CHAR(20) DEFAULT NULL, CHANGE priority_2 priority_2 CHAR(20) DEFAULT NULL, CHANGE priority_3 priority_3 CHAR(20) DEFAULT NULL, CHANGE priority_4 priority_4 CHAR(20) DEFAULT NULL, CHANGE priority_5 priority_5 CHAR(20) DEFAULT NULL, CHANGE priority_6 priority_6 CHAR(20) DEFAULT NULL, CHANGE password_forget_token password_forget_token CHAR(40) DEFAULT NULL, CHANGE layout layout CHAR(20) DEFAULT NULL, CHANGE palette palette CHAR(20) DEFAULT NULL, CHANGE access_custom_shortcuts access_custom_shortcuts JSON DEFAULT NULL COMMENT \'(DC2Type:json)\'');
    }
}
