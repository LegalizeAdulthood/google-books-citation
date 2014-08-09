var cheerio = require('cheerio');
var http = require('http');
var moment = require('moment');
var request = require('request');
var util = require('util');

function starts_with(text, prefix) {
    return text.substring(0, prefix.length) === prefix;
}

function citation_text(citation) {
    var text = '{{' + citation.publication,
        parts = citation.url.replace(/^.*\?/, '').replace(/#.*$/, '').split('&');
    for (var i = 0; i < parts.length; ++i) {
        if (starts_with(parts[i], 'id=')) {
            text += '\n| ' + parts[i];
        } else if (starts_with(parts[i], 'pg=')) {
            pieces = parts[i].match(/pg=(.+[^0-9])([0-9]+)$/);
            if (pieces[1] !== 'PA') {
                text += '\n| page_prefix=' + pieces[1];
            }
            text += '\n| page=' + pieces[2];
        }
    }
    text += '\n| title=';
    text += '\n| date=' + citation.date;
    citation.text = text + '\n}}';
}

function citation_from_html(citation, callback) {
    var $ = cheerio.load(citation.html),
        publication = $('h1.gb-volume-title'),
        date = $('span', publication);
    citation.date = moment(date.text()).format('MMMM D, YYYY');
    date.empty();
    citation.publication = publication.text().trim();
    delete citation.html;
    citation_text(citation);
    callback(null, citation);
}

function citation_from_url(url, callback) {
    request(url,
        function(err, res, data) {
            if (err) {
                callback(err);
            } else {
                citation_from_html({ url: url, html: data }, callback)
            }
        });
}

function main(args) {
    citation_from_url(args[2], function(err, data) {
        console.log(data.text);
    });
}

main(process.argv);
