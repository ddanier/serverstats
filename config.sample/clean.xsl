<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" xmlns:stats="urn:serverstats" >
<xsl:output method="xml" omit-xml-declaration="yes" indent="yes" encoding="utf-8" version="1.0" />
<xsl:strip-space elements="*" />
<xsl:param name="source" />
<xsl:template match="@*|*|text()">
        <xsl:copy-of select="." />
</xsl:template>

<xsl:template match="*" />

<xsl:template match="/" >
	<xsl:apply-templates />
</xsl:template>

<xsl:template match="stats:*">
        <xsl:copy>
		<xsl:copy-of select="@*" />
		<xsl:apply-templates />
        </xsl:copy>
</xsl:template>


<xsl:template match="stats:template">
       <xsl:apply-templates />
</xsl:template>


</xsl:stylesheet>
