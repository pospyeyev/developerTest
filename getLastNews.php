<?php
	/**
	* ������� ��������� ������� ��������� �������� 
	* @param string $url_channel ������ �� RSS �����
	* @param int $TopCount ���������� ���������� ��������
	* @return array
	*/
	function GetLastNewsFromRSS($url_channel, $TopCount){
	
		$content = file_get_contents($url_channel); 
		$AllNews = new SimpleXmlElement($content);
		$LastNews=array();
		$CountNews=0;
		
		foreach($AllNews->channel->item as $news) {
			
			$OneNews["TITLE"]=$news->title;
			$OneNews["LINK"]=$news->link;
			$OneNews["PREVIEW_TEXT"]=$news->description;
			$LastNews[]=$OneNews;
			$count++;
			if ($count==$TopCount){ break;}
			
		}
		
		return $LastNews;
		
	}
	
	
	/* ������ ������*/
	$urlNews = "https://lenta.ru/rss";
	$CountNews=5;
	$GetLastNews=GetLastNewsFromRSS($urlNews, $CountNews);
	
	foreach($GetLastNews as $news) {
		echo $news["TITLE"]." ".$news["LINK"]." ".$news["PREVIEW_TEXT"].PHP_EOL;
	}