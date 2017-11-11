#!/usr/bin/perl

use strict;
use warnings;
use FCGI;
use CGI;
use Image::Size;
use Data::Dumper;

# use:
# https://www.w3schools.com/css/tryit.asp?filename=trycss_position_fixed

local our $q;
my $request = FCGI::Request();

my $base   = "/pool0";
my $target = 300;

sub video_player {
	my $q     = shift;
	my $video = shift;

	print "<video controls>
		<source src=\"$video\" type=\"video/mp4\">
		Your browser does not support the video tag.
		</video><br />\n";
}

sub main {
	my $q = shift;

	print $q->header(
		-expires => "0m",
		"-Cache-Control" => "max-age=0,private",
	);

	my $style = <<'EOT';
	.flex-container {
		display: -webkit-flex;
		display: flex;  
		-webkit-flex-flow: row wrap;
		flex-flow: row wrap;
		text-align: center;
	}
	
	.flex-container > * {
		padding: 15px;
		-webkit-flex: 1 100%;
		flex: 1 100%;
	}
	
	.article {
		text-align: left;
	}

	.article video {
		background: #ffffff;
		width:      650px;
		height:     400px;
		left:       250px;
		position:   fixed;
		top:        0px;
	}
	
	header {background: black;color:white;}
	footer {background: #aaa;color:white;}
	.nav {
		background:#eee;
		width: 300px;
	}
	
	.nav ul {
		list-style-type: none;
		padding: 0;
	}
	.nav ul a {
		text-decoration: none;
	}
	
	@media all and (min-width: 200px) {
		.nav {text-align:left;-webkit-flex: 1 auto;flex:1 auto;-webkit-order:1;order:1;}
		.article {-webkit-flex:5 0px;flex:5 0px;-webkit-order:2;order:2;}
		footer {-webkit-order:3;order:3;}
	}
EOT

	my $script = <<'EOT';
function get_data() {
	var xmlhttp;
	if (window.XMLHttpRequest) {
		// code for IE7+, Firefox, Chrome, Opera, Safari
		xmlhttp = new XMLHttpRequest();
	} else {
		// code for IE6, IE5
		xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
	}
	xmlhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			document.getElementById('nav').innerHTML += this.responseText;
		}
	};

	var lines      = document.getElementById('nav').innerHTML.split( '\n' );
	var parameters = "count=";
	parameters    += lines.length;

	xmlhttp.open( "POST", "/www/fcgi/gallery.fcgi", true );
	xmlhttp.setRequestHeader( "Content-type", "application/x-www-form-urlencoded" )
	xmlhttp.send( parameters )
}

function limit_button( limit ) {
	var xmlhttp;
	if (window.XMLHttpRequest) {
		// code for IE7+, Firefox, Chrome, Opera, Safari
		xmlhttp = new XMLHttpRequest();
	} else {
		// code for IE6, IE5
		xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
	}
	xmlhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			clear_thumbs();
			topic_buttons();
			document.getElementById('nav').innerHTML += this.responseText;
		}
	};

	var parameters = "limit=";
	parameters    += limit;

	xmlhttp.open( "POST", "/www/fcgi/gallery.fcgi", true );
	xmlhttp.setRequestHeader( "Content-type", "application/x-www-form-urlencoded" )
	xmlhttp.send( parameters )
}


function yHandler() {
	var wrap = document.getElementById('wrap');
	var contentHeight = wrap.offsetHeight;
	var yOffset = window.pageYOffset;
	var y = yOffset + window.innerHeight;
	if( y >= contentHeight ){
		// Ajax call to get more dynamic data goes here
		// wrap.innerHTML += '<div class="newData"></div>';
		get_data();
	}
	var status = document.getElementById('status');
	//status.innerHTML = contentHeight+" | "+y;
}

function set_player_data( video_url ) {
	document.getElementById('wrap').innerHTML = '<video controls><source src="' + video_url + '" type="video/mp4">Your browser does not support the video tag.</video><br />';
}

function clear_thumbs() {
	document.getElementById('nav').innerHTML = '';
}

function topic_buttons() {
	var h = new Object();

	h['youtube/twentytrucks'] = 'Trucks';
	h['digger']               = 'Diggers';
	h['veritasium']           = 'Veritasium';
	h['hooplakidzlab']        = 'HooplakidzLab';

	for( var k in h ) {
		if( h.hasOwnProperty( k ) ) {
			document.getElementById('nav').innerHTML += '<button onclick="limit_button(\'' + k + '\')">' + h[k] + '</button><br><br>\n';
		}
	}
	document.getElementById('nav').innerHTML += '<br/>\n';
}

function reload_page() {
	location.reload();
}

function startup() {
	clear_thumbs();
	topic_buttons();
	get_data();
}

window.onscroll = yHandler;
EOT

	print $q->start_html(
		-script => $script,
		-title  => 'Ben YouTube',
		-style  => { -code => $style },
		-onLoad => "javascript:startup();",
	);

	my ($video) = $q->param( "video" );
	my ($limit) = $q->param( "limit" );

	#print $q->div( { -name => "flex-container" }, "flex container" );
	print '<div class="flex-container">' . "\n";
	print '<header><a name="top"></a><h1>nevtube</h1></header>' . "\n";

	print "<nav class=\"nav\" id=\"nav\"><ul>\n";
	
	print "</ul></nav>\n";
	
	print "<article class=\"article\" id=\"wrap\">\n";
	if( defined( $video ) ) {
		video_player( $q, $video );
	}
	print "</article>\n";


	print "<footer id=\"footer\">
		<a href=\"/#top\">
		<button onclick=\"reload_button()\">Reload page</button>
		</a></footer>";
	print "</div>\n";

	print $q->end_html();
}

while( $request->Accept() >= 0 ) {
        my $e = $request->GetEnvironment();
        undef @CGI::QUERY_PARAM;
        $q = CGI->new();

        main( $q );
}

