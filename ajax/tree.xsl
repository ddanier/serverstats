<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" xmlns="http://www.w3.org/1999/xhtml" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:xf="http://www.w3.org/2002/xforms" xmlns:xi="http://www.w3.org/2001/XInclude" xmlns:stats="urn:serverstats" exclude-result-prefixes="xhtml xi xf stats">
<xsl:output method="xml" omit-xml-declaration="yes" indent="yes" encoding="utf-8" version="1.0" />
<xsl:strip-space elements="*" />
<xsl:param name="modulepath" />
<xsl:template match="@*|*|text()">
        <xsl:copy-of select="." />
</xsl:template>

<xsl:template match="*" />

<xsl:template match="/">
	<ul id="tree" class="tree" xmlns="http://www.w3.org/1999/xhtml">
		<xsl:apply-templates/>
		<li><a href="javascript:loadgraphs('graphs.php?filter=impact:important');">Important</a></li>
		<li><a href="javascript:loadgraphs('graphs.php?filter=impact:critical');">Critical</a></li>
	</ul>	
</xsl:template>

<xsl:template match="stats:node">
        <li><a href="javascript:loadgraphs('graphs.php?filter={stats:filter}');" class="file"><xsl:value-of select="stats:title" /></a>
	<xsl:if test="stats:node">
	<ul>
		<xsl:apply-templates/>
        </ul>
	</xsl:if>
	</li>
</xsl:template>

</xsl:stylesheet>
