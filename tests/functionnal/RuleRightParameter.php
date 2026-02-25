<?php

/**
 * ---------------------------------------------------------------------
 * GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2015-2022 Teclib' and contributors.
 *
 * http://glpi-project.org
 *
 * based on GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2003-2014 by the INDEPNET Development Team.
 *
 * ---------------------------------------------------------------------
 */

namespace tests\units;

use DbTestCase;

class RuleRightParameter extends DbTestCase
{
    protected function missingClassesProvider(): array
    {
        return [
            ['ApplianceEnvironment'],
            ['ApplianceType'],
            ['CartridgeItem_PrinterModel'],
            ['ChangeCost'],
            ['ChangeTemplate'],
            ['ChangeTemplateHiddenField'],
            ['ChangeTemplateMandatoryField'],
            ['ChangeTemplatePredefinedField'],
            ['Change_Group'],
            ['Change_Problem'],
            ['Change_Supplier'],
            ['Change_User'],
            ['ClusterType'],
            ['CommonItilObject_Item'],
            ['ComputerType'],
            ['Computer_SoftwareLicense'],
            ['Computer_SoftwareVersion'],
            ['ConsumableItemType'],
            ['DeviceBatteryModel'],
            ['DeviceBatteryType'],
            ['DeviceCaseModel'],
            ['DeviceCaseType'],
            ['DeviceControlModel'],
            ['DeviceDriveModel'],
            ['DeviceFirmwareModel'],
            ['DeviceFirmwareType'],
            ['DeviceGenericModel'],
            ['DeviceGenericType'],
            ['DeviceGraphicCardModel'],
            ['DeviceHardDriveModel'],
            ['DeviceMemoryModel'],
            ['DeviceMemoryType'],
            ['DeviceMotherBoardModel'],
            ['DeviceNetworkCardModel'],
            ['DevicePci'],
            ['DevicePciModel'],
            ['DevicePowerSupplyModel'],
            ['DeviceProcessorModel'],
            ['DeviceSoundCardModel'],
            ['EnclosureModel'],
            ['FQDNLabel'],
            ['Group_Problem'],
            ['IPNetmask'],
            ['ITILFollowupTemplate'],
            ['ITILTemplateField'],
            ['ITILTemplateHiddenField'],
            ['ITILTemplateMandatoryField'],
            ['ITILTemplatePredefinedField'],
            ['Item_DevicePci'],
            ['LevelAgreement'],
            ['LevelAgreementLevel'],
            ['MonitorModel'],
            ['MonitorType'],
            ['NetworkEquipmentModel'],
            ['NetworkEquipmentType'],
            ['NetworkInterface'],
            ['NetworkPortDialup'],
            ['NetworkPortFiberchannel'],
            ['NetworkPortLocal'],
            ['NetworkPortWifi'],
            ['OperatingSystemServicePack'],
            ['OperatingSystemVersion'],
            ['PDUModel'],
            ['PDUType'],
            ['PDU_Rack'],
            ['PassiveDCEquipmentModel'],
            ['PassiveDCEquipmentType'],
            ['PeripheralModel'],
            ['PeripheralType'],
            ['PhoneModel'],
            ['PhonePowerSupply'],
            ['PhoneType'],
            ['PrinterModel'],
            ['PrinterType'],
            ['ProblemCost'],
            ['ProblemTemplate'],
            ['ProblemTemplateHiddenField'],
            ['ProblemTemplateMandatoryField'],
            ['ProblemTemplatePredefinedField'],
            ['Problem_Supplier'],
            ['Problem_Ticket'],
            ['Problem_User'],
            ['RackModel'],
            ['RackType'],
            ['RuleAssetCollection'],
            ['RuleDictionnaryComputerModel'],
            ['RuleDictionnaryComputerModelCollection'],
            ['RuleDictionnaryComputerType'],
            ['RuleDictionnaryComputerTypeCollection'],
            ['RuleDictionnaryDropdown'],
            ['RuleDictionnaryManufacturer'],
            ['RuleDictionnaryManufacturerCollection'],
            ['RuleDictionnaryMonitorModel'],
            ['RuleDictionnaryMonitorModelCollection'],
            ['RuleDictionnaryMonitorType'],
            ['RuleDictionnaryMonitorTypeCollection'],
            ['RuleDictionnaryNetworkEquipmentType'],
            ['RuleDictionnaryNetworkEquipmentTypeCollection'],
            ['RuleDictionnaryOperatingSystemArchitecture'],
            ['RuleDictionnaryOperatingSystemArchitectureCollection'],
            ['RuleDictionnaryOperatingSystemServicePack'],
            ['RuleDictionnaryOperatingSystemServicePackCollection'],
            ['RuleDictionnaryOperatingSystemVersion'],
            ['RuleDictionnaryOperatingSystemVersionCollection'],
            ['RuleDictionnaryPeripheralModel'],
            ['RuleDictionnaryPeripheralModelCollection'],
            ['RuleDictionnaryPeripheralType'],
            ['RuleDictionnaryPeripheralTypeCollection'],
            ['RuleDictionnaryPhoneModel'],
            ['RuleDictionnaryPhoneModelCollection'],
            ['RuleDictionnaryPhoneType'],
            ['RuleDictionnaryPhoneTypeCollection'],
            ['RuleDictionnaryPrinterModel'],
            ['RuleDictionnaryPrinterModelCollection'],
            ['RuleDictionnaryPrinterType'],
            ['RuleDictionnaryPrinterTypeCollection'],
            ['RuleImportEntityCollection'],
            ['RuleRightCollection'],
            ['RuleRightParameter'],
            ['SoftwareLicenseType'],
            ['SolutionType'],
            ['TicketCost'],
            ['TicketSatisfaction'],
            ['TicketTemplateHiddenField'],
            ['TicketTemplateMandatoryField'],
            ['VirtualMachineState'],
            ['VirtualMachineSystem'],
            ['VirtualMachineType'],
            ['WifiNetwork'],
        ];
    }

    /**
     * @dataprovider missingClassesProvider
     */
    public function testClassIsDeclaredAndLoadable(string $class): void
    {
        $fqcn = '\\' . $class;

        $previous_error_reporting = error_reporting();
        error_reporting($previous_error_reporting & ~E_DEPRECATED & ~E_USER_DEPRECATED);

        try {
            $this->boolean(class_exists($fqcn))->isTrue();

            $reflection = new \ReflectionClass($fqcn);
            $constructor = $reflection->getConstructor();

            if ($reflection->isInstantiable() && ($constructor === null || $constructor->getNumberOfRequiredParameters() === 0)) {
                $instance = $reflection->newInstance();
                $this->object($instance)->isInstanceOf($fqcn);
            }
        } finally {
            error_reporting($previous_error_reporting);
        }
    }
}
