<?php

namespace Travis;

use Travis\CLI;
use Travis\Date;
use Sunra\PhpSimple\HtmlDomParser;

class App
{
	public static function run()
	{
		// dir list
		$dir = 'https://www.monergism.com/450-free-ebooks-listed-alphabetically-author';

		// get books list
		$books = static::get_list($dir);

		// foreach book...
		$total = sizeof($books);
		foreach ($books as $num => $payload)
		{
			// report
			CLI::write(($num+1).'/'.$total.' - '.$payload['author'].' - '.$payload['title']);

			// define filename
			$folder = static::slug($payload['author']);
			$filename = static::slug($payload['title']);

			// set path
			$path = path('storage/'.$folder.'/'.$filename);

			// if not exists...
			if (!file_exists($path.' - #1.epub'))
			{
				// get links to downloads
				$downloads = static::get_book($payload['url'], $payload['title'], $payload['author']);

				// foreach download...
				foreach ($downloads as $k => $download)
				{
					// report
					CLI::info('Downloading version #'.($k+1).' of type "'.$download['extension'].'"...');

					// make dir
					@mkdir(path('storage/'.$folder));

					// report
					CLI::info($download['url']); // ??? this effects the outcome, but why????

					// download file
					$contents = @file_get_contents(static::fixurl($download['url']));

					// if found...
					if ($contents)
					{
						// save file
						file_put_contents($path.' - #'.($k+1).$download['extension'], $contents);
					}
					else
					{
						CLI::error('Unable to load url "'.$download['url'].'".');
					}
				}
			}
			else
			{
				// report
				CLI::error('File already downloaded.');
			}
		}

		// report
		CLI::write('Done.');
	}

	protected static function get_list($url)
	{
		// pull contents
		$contents = @file_get_contents(static::fixurl($url));

		// if not found...
		if (!$contents) CLI::fatal('No content found for page '.$url.'.');

		// parse as html
		$html = HtmlDomParser::str_get_html($contents);

		// get content
		$links = [];
		foreach ($html->find('div.field-item p') as $element)
		{
			if (strpos($element->plaintext, 'Calvin') !== false)
			{
				$content = $element->innertext;
				$authors = explode('<br />', $content);

				foreach ($element->find('a') as $e)
				{
					$links[] = [
						'url' => $e->href,
						'title' => $e->plaintext,
						'author' => null,
					];
				}
			}
		}

		// ignore list
		$ignore = [
			'http://www.amazon.com/gp/sendtokindle',
			'http://www.monergism.com/topics/free-ebooks',
    		'http://www.monergism.com/monergismcom-2015-crowdfunder',
		];

		// foreach link found...
		foreach ($links as $key => $value)
		{
			// run thru key to get author names
			foreach ($authors as $author)
			{
				// if this line includes this link...
				if (strpos($author, $value['url']) !== false)
				{
					// format author name
					$author = strip_tags($author);
					$author = str_ireplace([$value['url'], $value['title'], 'New!', 'Updated!'], ['', '', '', ''], $author);

					// save author name
					$links[$key]['author'] = $author;
				}
			}

			// foreach field of the payload...
			foreach ($links[$key] as $k => $v)
			{
				// if title or author...
				if (in_array($k, ['title', 'author']))
				{
					// format
					$links[$key][$k] = trim(str_replace(['&#39;', '&nbsp;'], ['\'', ''], $v));
				}
			}

			// delete ignores
			if (in_array($value['url'], $ignore)) unset($links[$key]);
		}

		// return
		return $links;
	}

	protected static function get_book($url, $title, $author)
	{
		// pull contents
		$contents = @file_get_contents(static::fixurl($url));

		// if not found...
		if (!$contents)
		{
			// report
			CLI::error('No content found for page "'.$url.'".');

			// bail
			return [];
		}

		// parse as html
		$html = HtmlDomParser::str_get_html($contents);

		// init
		$downloads = [];

		// foreach link...
		foreach($html->find('a') as $element)
		{
			if (stripos($element->href, '.epub') !== false)
			{
				$downloads[] = [
					'url' => $element->href,
					'title' => $title,
					'author' => $author,
					'extension' => '.epub',
				];
			}

			if (stripos($element->href, '.mobi') !== false)
			{
				$downloads[] = [
					'url' => $element->href,
					'title' => $title,
					'author' => $author,
					'extension' => '.mobi',
				];
			}
		}

		// if no downloads...
		if (!sizeof($downloads))
		{
			// report
			CLI::error('No downloads found for "'.$url.'".');
		}

		// return
		return $downloads;
	}

	protected static function slug($string)
	{
		return trim($string);
	}

	protected static function fixurl($string)
	{
		return str_replace([' '], ['%20'], $string);
	}
}