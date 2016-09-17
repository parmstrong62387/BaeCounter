<?php
$DEFAULT_THEME = "cat";

function getCurrentTheme() {
	$currentTheme = $_GET["theme"];
	if (!isset($currentTheme)) {
		$currentTheme = $DEFAULT_THEME;
	}

	return $currentTheme;
}

function getThemes() {
	$themes = array();
	$currentTheme = getCurrentTheme();
	
	$themeDirs = scandir("themes");
	foreach ($themeDirs as $themeDir) {
		if ($themeDir == '.' || $themeDir == '..') {
			continue;
		}

		$themeObj = array();
		$themeObj["name"] = $themeDir;
		$themeObj["friendlyName"] = ucfirst($themeDir);
		$themeObj["selected"] = basename($themeDir) == $currentTheme ? "selected" : "";

		array_push($themes, $themeObj);
	}

	return $themes;
}
?>