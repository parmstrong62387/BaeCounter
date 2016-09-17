<?php
function getCurrentTheme() {
	$DEFAULT_THEME = "cat";
	
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
		$themeObj["friendlyName"] = ucwords(str_replace("_", " ", $themeDir));
		$themeObj["selected"] = basename($themeDir) == $currentTheme ? "selected" : "";

		array_push($themes, $themeObj);
	}

	return $themes;
}
?>