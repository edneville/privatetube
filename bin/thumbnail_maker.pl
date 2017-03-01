#!/usr/bin/perl

use strict;
use warnings;
use Image::Size;

my $host   = `hostname`;
my $thumbs = $ENV{'NAS'};

if( $host =~ $ENV{'NASHOST'} ) {
	$thumbs = $ENV{'NASLOCALPATH'}
}

my $thumb_www = "www/img/thumbnails";

`mkdir -p "$thumbs"`;

sub main {
	while( my $line = <> ) {
		chomp $line;
		my ($wwwpath) = $line =~ m|^$thumb_www(.*)|;
		my ($x, $y, $data) = imgsize( "${thumbs}/${line}" );
		my ($film) = $wwwpath =~ m|^(.*)_200\.png$|;

		$film .= ".mp4";
		print "$line\0$wwwpath\0$film\0$x\0$y\n";
	}
}

main;
