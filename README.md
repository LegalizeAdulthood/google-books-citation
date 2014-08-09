This is a simple nodejs script to take a google books URL and emit a google books citation template that is suitable for use in the [Terminals Wiki](http://terminals.classiccmp.org).  Example:

```
> node citation.js "http://books.google.com/books?id=n5qBImUV6NQC&lpg=PA63-IA15&pg=PA63-IA15#v=onepage&q&f=false"
{{Computerworld
| id=n5qBImUV6NQC
| page_prefix=PA63-IA
| page=15
| title=
| date=September 23, 1985
}}
```

Note that the URL must be quoted if copy/pasted from the browser as it will contain the query character `?` which is a shell metacharacter.

The script fetches the URL and uses it to identify the publication name (Computerworld), the page number (15) and the date (September 23, 1985).  It can't identify the appropriate article title, so it leaves that blank and this must be filled in manually.

On Windows, it can be handy to simply feed the output of the script into `clip` to get the output onto the clipboard.  The provided batch file `cite.bat` is a convenience that runs node with the given url and pipes to `clip`.
