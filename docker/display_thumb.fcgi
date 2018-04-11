#!/usr/bin/perl

use strict;
use warnings;
use FCGI;
use CGI;
use DBI;
use Image::Size;
use Data::Dumper;

# use:
# https://www.w3schools.com/css/tryit.asp?filename=trycss_position_fixed

require( "/usr/local/data/media_common.inc" );

my $request = FCGI::Request();
my $target = 300;

sub main {
	my $q  = shift;
	my $db = shift;
	my ($thumb) = $q->param( "thumb" );
	$db->do('SET bytea_output=escape');
	my $sql = "select * from " . table() . " where path = ?";
	my $sth = $db->prepare( $sql );
	$sth->execute( $thumb );
	
	my $hr = $sth->fetchrow_hashref();

	if( !$hr ) {
		print $q->header(
			-expires => "0m",
			"-Cache-Control" => "max-age=0,private",
			-status => "404",
			-type => "text/plain",
		);

		print "Cannot find $thumb\n";
		$sth->finish;
		return;
	}

	print $q->header(
		-expires => "0m",
		"-Cache-Control" => "max-age=0,private",
		-status => "200",
		-type => "image/jpeg",
	);

	print $hr->{'thumbnail'};
	$sth->finish;
}

my $db = dbconn();
while( $request->Accept() >= 0 ) {
        my $e = $request->GetEnvironment();
        undef @CGI::QUERY_PARAM;
        my $q = CGI->new();

        main( $q, $db );
}

