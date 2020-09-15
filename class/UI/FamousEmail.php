<?php
namespace App\UI;

class FamousEmail {
	const EMAIL_LIST = [
		"katherine.johnson@nasa.gov", // 1918 human computer for Project Mercury & Apollo 11
		"evelyn.boyd.granville@ibm.com", // 1956 IBM 650 programmer worked on Apollo missions
		"roy.l.clay@hp.com", // 1965 introduced HP into computer market
		"clarance.ellis@uillinois.edu", // 1943 first African-American to earn Ph.D - developed on early OOP languages and icon-based GUIs
		"mark.dean@ibm.com", // 1981 co-created IBM personal computer
		"john.henry.thompson@macromedia.com", // 1988 chief scientist at Macromedia
		"kimberly.bryant@blackgirlscode.com", // 2011 founded Black Girls Code
		"margaret.hamilton@mit.edu", // 1961 pioneered software engineering working on US air defence systems
		"steve.shirley@f-international.com", // 1988 Stephanie "Steve" Shirley was the world's first "freelance" programmer
		"joan.clark@bletchleypark.org.uk", // 1946 crowdfunded restoration of Bletchley Park
	];

	public function getRandom():string {
		return self::EMAIL_LIST[array_rand(self::EMAIL_LIST)];
	}
}