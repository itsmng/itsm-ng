<?php

namespace tests\units;

use DbTestCase;

class IPNetwork extends DbTestCase
{
    public function testNetworkNameAndAliasWithIps()
    {
        $this->login();

        $fqdn = new \FQDN();
        $domain = 'networking-' . strtolower($this->getUniqueString()) . '.example';
        $fqdns_id = (int)$fqdn->add([
           'name' => 'fqdn-' . $this->getUniqueString(),
           'fqdn' => $domain,
        ]);
        $this->integer($fqdns_id)->isGreaterThan(0);

        $networkname = new \NetworkName();
        $networknames_id = (int)$networkname->add([
           'name'         => 'host-' . strtolower($this->getUniqueString()),
           'fqdns_id'     => $fqdns_id,
           '_ipaddresses' => ['-1' => '10.42.0.10'],
        ]);
        $this->integer($networknames_id)->isGreaterThan(0);
        $this->boolean($networkname->getFromDB($networknames_id))->isTrue();
        $this->integer((int)$networkname->fields['fqdns_id'])->isEqualTo($fqdns_id);
        $this->string($networkname->fields['name'])->contains('host-');

        $this->integer((int)countElementsInTable(
            \IPAddress::getTable(),
            [
                'itemtype' => 'NetworkName',
                'items_id' => $networknames_id,
                'name'     => '10.42.0.10',
            ]
        ))->isEqualTo(1);

        $alias = new \NetworkAlias();
        $alias_id = (int)$alias->add([
           'networknames_id' => $networknames_id,
           'name'            => 'alias-' . strtolower($this->getUniqueString()),
           'fqdns_id'        => $fqdns_id,
        ]);
        $this->integer($alias_id)->isGreaterThan(0);
        $this->boolean($alias->getFromDB($alias_id))->isTrue();
        $this->string(\NetworkAlias::getInternetNameFromID($alias_id))->contains($alias->fields['name']);
    }

    public function testFqdnAndFqdnLabelValidation()
    {
        $this->login();

        $fqdn = new \FQDN();
        $this->integer((int)$fqdn->add([
           'name' => 'invalid-fqdn-' . $this->getUniqueString(),
           'fqdn' => 'invalid..example',
        ]))->isEqualTo(0);
        $this->hasSessionMessages(ERROR, ['FQDN is not valid']);

        $networkname = new \NetworkName();
        $this->integer((int)$networkname->add([
           'name' => '-badlabel',
        ]))->isEqualTo(0);
        $this->hasSessionMessages(ERROR, ['Invalid internet name: -badlabel']);
    }

    public function testIpNetworkAddAndRejectInvalid()
    {
        $this->login();

        $net = new \IPNetwork();
        $suffix = (int)mt_rand(50, 200);
        $id = (int)$net->add([
           'name'       => 'net-' . $this->getUniqueString(),
           'entities_id' => 0,
           'network'    => "10.$suffix.20.0/24",
           'gateway'    => "10.$suffix.20.1",
           'addressable' => 1,
        ]);
        $this->integer($id)->isGreaterThan(0);

        $this->integer((int)$net->add([
           'name'       => 'invalid-net-' . $this->getUniqueString(),
           'entities_id' => 0,
           'network'    => "10.$suffix.20.0",
           'gateway'    => "10.$suffix.20.1",
        ]))->isEqualTo(0);
        $this->hasSessionMessages(ERROR, ['Invalid input format for the network']);
    }

    public function testIpAddressAutoLinksToIpNetwork()
    {
        $this->login();

        $suffix = (int)mt_rand(50, 200);
        $ipnetwork = new \IPNetwork();
        $ipnetworks_id = (int)$ipnetwork->add([
           'name'       => 'autolink-net-' . $this->getUniqueString(),
           'entities_id' => 0,
           'network'    => "10.$suffix.30.0/24",
           'gateway'    => "10.$suffix.30.1",
        ]);
        $this->integer($ipnetworks_id)->isGreaterThan(0);

        $networkname = new \NetworkName();
        $networknames_id = (int)$networkname->add([
           'name' => 'autolink-host-' . strtolower($this->getUniqueString()),
        ]);
        $this->integer($networknames_id)->isGreaterThan(0);

        $ipaddress = new \IPAddress();
        $ipaddresses_id = (int)$ipaddress->add([
           'itemtype' => 'NetworkName',
           'items_id' => $networknames_id,
           'name'     => "10.$suffix.30.20",
        ]);
        $this->integer($ipaddresses_id)->isGreaterThan(0);

        $this->integer((int)countElementsInTable(
            \IPAddress_IPNetwork::getTable(),
            [
                'ipaddresses_id' => $ipaddresses_id,
                'ipnetworks_id'  => $ipnetworks_id,
            ]
        ))->isEqualTo(1);
    }

    public function testIpNetworkVlanAssignAndUnassign()
    {
        $this->login();

        $suffix = (int)mt_rand(50, 200);
        $ipnetwork = new \IPNetwork();
        $ipnetworks_id = (int)$ipnetwork->add([
           'name'       => 'vlan-net-' . $this->getUniqueString(),
           'entities_id' => 0,
           'network'    => "10.$suffix.40.0/24",
           'gateway'    => "10.$suffix.40.1",
        ]);
        $this->integer($ipnetworks_id)->isGreaterThan(0);

        $vlan = new \Vlan();
        $vlans_id = (int)$vlan->add([
           'name' => 'vlan-' . $this->getUniqueString(),
           'tag'  => (int)mt_rand(200, 3500),
        ]);
        $this->integer($vlans_id)->isGreaterThan(0);

        $relation = new \IPNetwork_Vlan();
        $relation_id = (int)$relation->assignVlan($ipnetworks_id, $vlans_id);
        $this->integer($relation_id)->isGreaterThan(0);

        $this->boolean($relation->unassignVlan($ipnetworks_id, $vlans_id))->isTrue();
        $this->integer((int)countElementsInTable(
            \IPNetwork_Vlan::getTable(),
            [
                'ipnetworks_id' => $ipnetworks_id,
                'vlans_id'      => $vlans_id,
            ]
        ))->isEqualTo(0);
    }

    public function testNetpointExecuteAddMulti()
    {
        $this->login();
        $this->setEntity(0, true);

        $locations_id = getItemByTypeName('Location', '_location01', true);
        $prefix = 'netpoint-' . strtolower($this->getUniqueString()) . '-';
        $netpoint = new \Netpoint();

        $netpoint->executeAddMulti([
            'entities_id'  => 0,
            'locations_id' => $locations_id,
            '_before'      => $prefix,
            '_after'       => '',
            '_from'        => 1,
            '_to'          => 3,
        ]);

        foreach ([1, 2, 3] as $index) {
            $this->integer((int)countElementsInTable(
                \Netpoint::getTable(),
                [
                    'name'         => $prefix . $index,
                    'locations_id' => $locations_id,
                    'entities_id'  => 0,
                ]
            ))->isEqualTo(1);
        }
    }

}
