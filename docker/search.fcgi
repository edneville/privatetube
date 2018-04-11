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
my $target = 200;

sub main {
	my $q  = shift;
	my $db = shift;
	my ($query) = $q->param( "query" );
	my $limit = 20;

	$query = "%" . $query . "%";
	if( $query !~ /^%.%$/ ) {
		$limit = 200;
	}

	my $sql = "select * from " . table() . " where path ilike ? order by random() limit ?";
	my $sth = $db->prepare( $sql );
	$sth->execute( $query, $limit );

	my $found = 0;

	while( my $hr = $sth->fetchrow_hashref() ) {
		if( !( family_friendly( $hr->{'path'} ) ) ) {
			next;
		}
		if( ! -f $hr->{'path'} ) {
			next;
		}
		if( !$found ) {
			print $q->header(
					-expires => "0m",
					"-Cache-Control" => "max-age=0,private",
					-status => "200",
					-type => "text/html",
					);

			print $q->start_html();
		}

		my $name = $hr->{'name'};
		$name = tidy_name( $name );

		my $thumbsrc = "/cgi-bin/display_thumb.fcgi?thumb=" . $hr->{'path'};
		my ($x, $y, $err) = imgsize( \$hr->{'thumbnail'} );
		if( !defined($x) || !defined($y) ) {
			print STDERR $err, "\n";
		}
		my $ratio  = $target / $x;
		my $height = int( $y * $ratio );


		print "<img onclick=\"javascript:set_player_data( '",
		      $hr->{'path'},
		      "' )\" src=\"$thumbsrc\" width=\"${target}\" height=\"${height}\"><br />$name<br />\n";

		$found = 1;
	}
	$sth->finish;

	if( !$found ) {
		print $q->header(
				-expires => "0m",
				"-Cache-Control" => "max-age=0,private",
				-status => "404",
				-type => "text/plain",
				);

		print "Cannot find $query\n";
		return;
	}
}

my $db = dbconn();
$db->do('SET bytea_output=escape');

while( $request->Accept() >= 0 ) {
	my $e = $request->GetEnvironment();
	undef @CGI::QUERY_PARAM;
	my $q = CGI->new();

	main( $q, $db );
}
