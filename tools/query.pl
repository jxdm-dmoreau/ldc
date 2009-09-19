#!/usr/bin/perl -w

use HTTP::Request::Common;
use LWP::UserAgent;
use JSON;


$HTTP_SERVER         = 'http://192.168.1.6:3333';
$RPC_PATH            = '/rpc/';
$RPC_ADD_OPEATION    = 'RPC_add_operation.php';
$RPC_UPDATE_OPEATION = 'RPC_update_operation.php';
$RPC_DEL_OPEATION    = 'RPC_del_operation.php';
$RPC_GET_OPEATION    = 'RPC_get_operation.php';

$URL_ADD_OPERATION    = $HTTP_SERVER.$RPC_PATH.$RPC_ADD_OPEATION;
$URL_UPDATE_OPERATION = $HTTP_SERVER.$RPC_PATH.$RPC_UPDATE_OPEATION;
$URL_DEL_OPERATION    = $HTTP_SERVER.$RPC_PATH.$RPC_DEL_OPEATION;
$URL_GET_OPERATION    = $HTTP_SERVER.$RPC_PATH.$RPC_GET_OPEATION;



sub send_json
{
    my ($json, $url) = @_;
    print "==> $json\n";
    my $ua = LWP::UserAgent->new;
    $ret = $ua->request(POST "$url", [json   => $json ]);
    if ($ret->is_success) {
	return $ret->content;
    }
    print STDERR $ret->status_line, "\n";
}

sub check_json
{
    my ($json) = @_;
    print "<== $json\n";
    my $hash_json = from_json $json;
    if ($hash_json->{'result'} eq 'true') {
	return $hash_json;
    }
    print STDERR "ERROR - $json\n";
    exit 1;
}

# ajouter une opération
$hash_json_orig = {
    date        => '2009-03-03',
    value       => 15,
    description => "coucou c\'est une description",
    confirm     => 1,
    cats        => [ { 'id' => 1, value => 12 },
		    { 'id' => 2, value => 3 } ],
    labels      => [ "leader-price", "carrefour" ]
    };

$json      = to_json $hash_json_orig;
$json      = send_json $json, $URL_ADD_OPERATION;
$hash_json = check_json $json;


# on récupère l'opération créée
$op_id = $hash_json->{'id'};
$hash_json = { id => $op_id };
$json      = to_json $hash_json;
$json      = send_json $json, $URL_GET_OPERATION;
$hash_json = check_json $json;

print STDERR "ERROR in date\n" if $hash_json_orig->{'date'} ne $hash_json->{'date'};
print STDERR "ERROR in value\n" if $hash_json_orig->{'value'} ne $hash_json->{'value'};
print STDERR "ERROR in description\n" if $hash_json_orig->{'description'} ne $hash_json->{'description'};
print STDERR "ERROR in confirm\n" if $hash_json_orig->{'confirm'} ne $hash_json->{'confirm'};
print STDERR "ERROR in cats 0 id\n" if $hash_json_orig->{'cats'}->[0]->{'id'} ne $hash_json->{'cats'}->[0]->{'id'};
print STDERR "ERROR in cats 0 value\n" if $hash_json_orig->{'cats'}->[0]->{'value'} ne $hash_json->{'cats'}->[0]->{'value'};
print STDERR "ERROR in cats 1 id\n" if $hash_json_orig->{'cats'}->[1]->{'id'} ne $hash_json->{'cats'}->[1]->{'id'};
print STDERR "ERROR incats 1 value\n" if $hash_json_orig->{'cats'}->[1]->{'value'} ne $hash_json->{'cats'}->[1]->{'value'};
print STDERR "ERROR in labels 0\n" if ($hash_json_orig->{'labels'}->[0] ne $hash_json->{'labels'}->[0] && $hash_json_orig->{'labels'}->[0] ne $hash_json->{'labels'}->[1]);
print STDERR "ERROR in labels 1\n" if ($hash_json_orig->{'labels'}->[1] ne $hash_json->{'labels'}->[1] && $hash_json_orig->{'labels'}->[0] ne $hash_json->{'labels'}->[1]);


# modifier une opération
$id = $hash_json->{'id'};
$hash_json_orig = {
    id          => $id,
    date        => '2009-01-21',
    value       => -10,
    description => "no comment",
    confirm     => 0,
    cats        => [ { 'id' => 1, value => -10 } ],
    labels      => [ "fourniture", "carrefour" ]
    };

$json      = to_json $hash_json_orig;
$json      = send_json $json, $URL_UPDATE_OPERATION;
$hash_json = check_json $json;


# on récupère l'opération modifiée
$hash_json = { id => $id };
$json      = to_json $hash_json;
$json      = send_json $json, $URL_GET_OPERATION;
$hash_json = check_json $json;

print STDERR "ERROR in date\n" if $hash_json_orig->{'date'} ne $hash_json->{'date'};
print STDERR "ERROR in value\n" if $hash_json_orig->{'value'} ne $hash_json->{'value'};
print STDERR "ERROR in description\n" if $hash_json_orig->{'description'} ne $hash_json->{'description'};
print STDERR "ERROR in confirm\n" if $hash_json_orig->{'confirm'} ne $hash_json->{'confirm'};
print STDERR "ERROR in cats 0 id\n" if $hash_json_orig->{'cats'}->[0]->{'id'} ne $hash_json->{'cats'}->[0]->{'id'};
print STDERR "ERROR in cats 0 value\n" if $hash_json_orig->{'cats'}->[0]->{'value'} ne $hash_json->{'cats'}->[0]->{'value'};
print STDERR "ERROR in labels 0\n" if ($hash_json_orig->{'labels'}->[0] ne $hash_json->{'labels'}->[0] && $hash_json_orig->{'labels'}->[0] ne $hash_json->{'labels'}->[1]);
print STDERR "ERROR in labels 0\n" if ($hash_json_orig->{'labels'}->[1] ne $hash_json->{'labels'}->[0] && $hash_json_orig->{'labels'}->[1] ne $hash_json->{'labels'}->[1]);



# suppresion de l'opération
$hash_json = { id => $op_id };
$json      = to_json $hash_json;
$json      = send_json $json, $URL_DEL_OPERATION;
$hash_json = check_json $json;


exit 0;
