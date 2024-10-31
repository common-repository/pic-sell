<?php

function pic_esc_json( $json, $html = false ) {
	return _wp_specialchars(
		$json,
		$html ? ENT_NOQUOTES : ENT_QUOTES, // Escape quotes in attribute nodes only.
		'UTF-8',                           // json_encode() outputs UTF-8 (really just ASCII), not the blog's charset.
		true                               // Double escape entities: `&amp;` -> `&amp;amp;`.
	);
}

function pic_check_html($html){
        $start =strpos($html, '<');
        $end  =strrpos($html, '>',$start);
      
        $len=strlen($html);
      
        if ($end !== false) {
          $string = substr($html, $start);
        } else {
          $string = substr($html, $start, $len-$start);
        }
        libxml_use_internal_errors(true);
        libxml_clear_errors();
        $xml = simplexml_load_string($string);
        if(count(libxml_get_errors())==0){
            return $html;
        }else{
            return "<p>HTML NON VALIDE</p>";
        }
}

/**
 * (Private) All Allowed Tags
 *
 * @ignore
 *
 * @link https://developer.mozilla.org/en-US/docs/Web/HTML/Element
 *
 * @return array
 */
function _pic_allowed_tags_all() {
	return array(
		// Document metadata.
		'head'  => prefix_allowed_global_attributes(),
		'link'  => array_merge(
			prefix_allowed_global_attributes(),
			array(
				'as'             => true,
				'disabled'       => true,
				'href'           => true,
				'hreflang'       => true,
				'importance'     => true,
				'integrity'      => true,
				'media'          => true,
				'referrerpolicy' => true,
				'rel'            => true,
				'sizes'          => true,
				'title'          => true,
				'type'           => true,
			)
		),
		'meta'  => array(
			'content' => true,
			'name' => true,
		),
		'style' => array_merge(
			prefix_allowed_global_attributes(),
			array(
				'type'  => true,
				'media' => true,
				'nonce' => true,
				'title' => true,
			)
		),
		'title' => prefix_allowed_global_attributes(),

		// Sectioning root.
		'body' => array_merge(
			prefix_allowed_global_attributes(),
			array(
				'onafterprint'     => true,
				'onbeforeprint'    => true,
				'onbeforeunload'   => true,
				'onblur'           => true,
				'onerror'          => true,
				'onfocus'          => true,
				'onhashchange'     => true,
				'onlanguagechange' => true,
				'onload'           => true,
				'onmessage'        => true,
				'onoffline'        => true,
				'ononline'         => true,
				'onpopstate'       => true,
				'onredo'           => true,
				'onresize'         => true,
				'onstorage'        => true,
				'onundo'           => true,
				'onunload'         => true,
			)
		),

		// Content Sectioning.
		'address'  => prefix_allowed_global_attributes(),
		'articles' => prefix_allowed_global_attributes(),
		'aside'    => prefix_allowed_global_attributes(),
		'footer'   => prefix_allowed_global_attributes(),
		'header'   => prefix_allowed_global_attributes(),
		'h1'       => prefix_allowed_global_attributes(),
		'h2'       => prefix_allowed_global_attributes(),
		'h3'       => prefix_allowed_global_attributes(),
		'h4'       => prefix_allowed_global_attributes(),
		'h5'       => prefix_allowed_global_attributes(),
		'h6'       => prefix_allowed_global_attributes(),
		'hgroup'   => prefix_allowed_global_attributes(),
		'main'     => prefix_allowed_global_attributes(),
		'nav'      => prefix_allowed_global_attributes(),
		'section'  => prefix_allowed_global_attributes(),

		// Text Content.
		'blockquote' => array_merge(
			prefix_allowed_global_attributes(),
			array(
				'cite' => true,
			)
		),
		'dd'         => array_merge(
			prefix_allowed_global_attributes(),
			array(
				'nowrap' => true,
			)
		),
		'div'        => prefix_allowed_global_attributes(),
		'dl'         => prefix_allowed_global_attributes(),
		'dt'         => prefix_allowed_global_attributes(),
		'figcaption' => prefix_allowed_global_attributes(),
		'figure'     => prefix_allowed_global_attributes(),
		'hr'         => array_merge(
			prefix_allowed_global_attributes(),
			array(
				'align'   => true, // Deprecated.
				'color'   => true,
				'noshade' => true, // Deprecated.
				'size'    => true, // Deprecated.
				'width'   => true, // Deprecated.
			)
		),
		'li'         => array_merge(
			prefix_allowed_global_attributes(),
			array(
				'value' => true,
			)
		),
		'ol'         => array_merge(
			prefix_allowed_global_attributes(),
			array(
				'reversed' => true,
				'start'    => true,
			)
		),
		'p'          => prefix_allowed_global_attributes(),
		'pre'        => array_merge(
			prefix_allowed_global_attributes(),
			array(
				'pre' => true,
			)
		),
		'ul'         => array_merge(
			prefix_allowed_global_attributes(),
			array(
				'compact' => true,
				'type'    => true,
			)
		),

		// Inline Text Sematics
		'a'      => array_merge(
			prefix_allowed_global_attributes(),
			array(
				'download' => true,
				'href' => true,
				'hreflang' => true,
				'ping' => true,
				'referrerpolicy' => true,
				'rel' => true,
				'target' => true,
				'type' => true,
			)
		),
		'abbr'   => array_merge(
			prefix_allowed_global_attributes(),
			array(
				'title' => true,
			)
		),
		'b'      => prefix_allowed_global_attributes(),
		'bdi'    => prefix_allowed_global_attributes(),
		'bdo'    => prefix_allowed_global_attributes(),
		'br'     => array_merge(
			prefix_allowed_global_attributes(),
			array(
				'clear' => true, // Deprecated.
			)
		),
		'cite'   => prefix_allowed_global_attributes(),
		'code'   => prefix_allowed_global_attributes(),
		'data'   => array_merge(
			prefix_allowed_global_attributes(),
			array(
				'value' => true,
			)
		),
		'dfn'    => prefix_allowed_global_attributes(),
		'em'     => prefix_allowed_global_attributes(),
		'i'      => prefix_allowed_global_attributes(),
		'kbd'    => prefix_allowed_global_attributes(),
		'mark'   => prefix_allowed_global_attributes(),
		'q'      => array_merge(
			prefix_allowed_global_attributes(),
			array(
				'cite' => true,
			)
		),
		'rb'     => prefix_allowed_global_attributes(),
		'rp'     => prefix_allowed_global_attributes(),
		'rt'     => prefix_allowed_global_attributes(),
		'rtc'    => prefix_allowed_global_attributes(),
		'ruby'   => prefix_allowed_global_attributes(),
		's'      => prefix_allowed_global_attributes(),
		'samp'   => prefix_allowed_global_attributes(),
		'small'  => prefix_allowed_global_attributes(),
		'span'   => prefix_allowed_global_attributes(),
		'strong' => prefix_allowed_global_attributes(),
		'sub'    => prefix_allowed_global_attributes(),
		'sup'    => prefix_allowed_global_attributes(),
		'time'   => array_merge(
			prefix_allowed_global_attributes(),
			array(
				'datetime' => true,
			)
		),
		'u'      => prefix_allowed_global_attributes(),
		'var'    => prefix_allowed_global_attributes(),
		'wbr'    => prefix_allowed_global_attributes(),

		// Image & Media.
		'area'  => array_merge(
			prefix_allowed_global_attributes(),
			array(
				'alt'            => true,
				'accesskey'      => true,
				'coords'         => true,
				'download'       => true,
				'href'           => true,
				'hreflang'       => true,
				'media'          => true,
				'name'           => true,
				'nohref'         => true,
				'ping'           => true,
				'referrerpolicy' => true,
				'rel'            => true,
				'shape'          => true,
				'tabindex'       => true,
				'target'         => true,
				'type'           => true,
			)
		),
		'audio' => array_merge(
			prefix_allowed_global_attributes(),
			array(
				'autoplay' => true,
				'buffered' => true,
				'controls' => true,
				'loop'     => true,
				'muted'    => true,
				'played'   => true,
				'preload'  => true,
				'src'      => true,
				'volume'   => true,
			)
		),
		'img'   => array_merge(
			prefix_allowed_global_attributes(),
			array(
				'align'          => true, // Deprecated.
				'alt'            => true,
				'border'         => true, // Deprecated.
				'crossorigin'    => true,
				'decoding'       => true,
				'height'         => true,
				'hspace'         => true, // Deprecated.
				'importance'     => true,
				'intrinsicsize'  => true,
				'ismap'          => true,
				'loading'        => true,
				'longdesc'       => true, // Deprecated.
				'name'           => true, // Deprecated.
				'onerror'        => true,
				'referrerpolicy' => true,
				'sizes'          => true,
				'src'            => true,
				'srcset'         => true,
				'usemap'         => true,
				'vspace'         => true, // Deprecated.
				'width'          => true,
			)
		),
		'map'   => array_merge(
			prefix_allowed_global_attributes(),
			array(
				'map' => true,
			)
		),
		'track' => array_merge(
			prefix_allowed_global_attributes(),
			array(
				'default' => true,
				'kind'    => true,
				'label'   => true,
				'src'     => true,
				'srclang' => true,
			)
		),
		'video' => array_merge(
			prefix_allowed_global_attributes(),
			array(
				'autoplay'             => true,
				'autoPictureInPicture' => true,
				'buffered'             => true,
				'controls'             => true,
				'controlslist'         => true,
				'crossorigin'          => true,
				'currentTime'          => true,
				'duration'             => true,
				'height'               => true,
				'intrinsicsize'        => true,
				'loop'                 => true,
				'muted'                => true,
				'playinline'           => true,
				'poster'               => true,
				'preload'              => true,
				'src'                  => true,
				'width'                => true,
			)
		),

		// Embedded Content.
		'embed'   => array_merge(
			prefix_allowed_global_attributes(),
			array(
				'height' => true,
				'src'    => true,
				'type'   => true,
				'width'  => true,
			)
		),
		'iframe'  => array_merge(
			prefix_allowed_global_attributes(),
			array(
				'align'           => true,
				'allow'           => true,
				'allowfullscreen' => true,
				'csp'             => true,
				'frameborder'     => true,
				'height'          => true,
				'importance'      => true,
				'loading'         => true,
				'longdesc'        => true,
				'marginheight'    => true,
				'marginwidth'     => true,
				'name'            => true,
				'referrerpolicy'  => true,
				'sandbox'         => true,
				'scrolling'       => true,
				'src'             => true,
				'srcdoc'          => true,
				'width'           => true,
			)
		),
		'object'  => array_merge(
			prefix_allowed_global_attributes(),
			array(
				'archive' => true, // Deprecated.
				'border' => true, // Deprecated.
				'classid' => true, // Deprecated.
				'codebase' => true, // Deprecated.
				'codetype' => true, // Deprecated.
				'data' => true,
				'declare' => true, // Deprecated.
				'form' => true,
				'height' => true,
				'name' => true,
				'standby' => true, // Deprecated.
				'tabindex' => true, // Deprecated.
				'type' => true,
				'typemustmatch' => true,
				'usemap' => true,
				'width' => true,
			)
		),
		'param'   => array_merge(
			prefix_allowed_global_attributes(),
			array(
				'name'      => true,
				'type'      => true, // Deprecated.
				'value'     => true,
				'valuetype' => true, // Deprecated.
			)
		),
		'picture' => prefix_allowed_global_attributes(),
		'source'  => array_merge(
			prefix_allowed_global_attributes(),
			array(
				'media'  => true,
				'sizes'  => true,
				'src'    => true,
				'srcset' => true,
				'type'   => true,
			)
		),

		// Scripting.
		'canvas'   => array_merge(
			prefix_allowed_global_attributes(),
			array(
				'height' => true,
				'width'  => true,
			)
		),
		'noscript' => prefix_allowed_global_attributes(),
		'script'   => array_merge(
			prefix_allowed_global_attributes(),
			array(
				'async'          => true,
				'crossorigin'    => true,
				'defer'          => true,
				'integrity'      => true,
				'language'       => true, // Deprecated.
				'nomodule'       => true,
				'referrerPolicy' => true,
				'src'            => true,
				'text'           => true,
				'type'           => true,
				'type.module'    => true,
			)
		),

		// Demarcating edits.
		'del' => array_merge(
			prefix_allowed_global_attributes(),
			array(
				'cite'     => true,
				'datetime' => true,
			)
		),
		'ins' => array_merge(
			prefix_allowed_global_attributes(),
			array(
				'cite'     => true,
				'datetime' => true,
			)
		),

		// Table Content.
		'caption'  => array_merge(
			prefix_allowed_global_attributes(),
			array(
				'align' => true, // Deprecated.
			)
		),
		'col'      => array_merge(
			prefix_allowed_global_attributes(),
			array(
				'align'   => true, // Deprecated.
				'bgcolor' => true, // Deprecated.
				'char'    => true, // Deprecated.
				'charoff' => true, // Deprecated.
				'span'    => true,
				'valign'  => true, // Deprecated.
				'width'   => true, // Deprecated.
			)
		),
		'colgroup'      => array_merge(
			prefix_allowed_global_attributes(),
			array(
				'align'   => true, // Deprecated.
				'bgcolor' => true, // Deprecated.
				'char'    => true, // Deprecated.
				'charoff' => true, // Deprecated.
				'span'    => true,
				'valign'  => true, // Deprecated.
				'width'   => true, // Deprecated.
			)
		),
		'table'    => array_merge(
			prefix_allowed_global_attributes(),
			array(
				'align'       => true, // Deprecated.
				'bgcolor'     => true, // Deprecated.
				'border'      => true, // Deprecated.
				'cellpadding' => true, // Deprecated.
				'cellspacing' => true, // Deprecated.
				'frame'       => true, // Deprecated.
				'rules'       => true, // Deprecated.
				'summary'     => true, // Deprecated.
				'width'       => true, // Deprecated.
			)
		),
		'tbody'    => array_merge(
			prefix_allowed_global_attributes(),
			array(
				'align'   => true, // Deprecated.
				'bgcolor' => true, // Deprecated.
				'char'    => true, // Deprecated.
				'charoff' => true, // Deprecated.
				'valign'  => true, // Deprecated.
			)
		),
		'td'       => array_merge(
			prefix_allowed_global_attributes(),
			array(
				'abbr'    => true, // Deprecated.
				'align'   => true, // Deprecated.
				'axis'    => true, // Deprecated.
				'bgcolor' => true, // Deprecated.
				'char'    => true, // Deprecated.
				'charoff' => true, // Deprecated.
				'colspan' => true,
				'headers' => true,
				'rowspan' => true,
				'scope'   => true, // Deprecated.
				'valign'  => true, // Deprecated.
				'width'   => true, // Deprecated.
			)
		),
		'modele_td'       => array_merge(
			prefix_allowed_global_attributes(),
			array(
				'abbr'    => true, // Deprecated.
				'align'   => true, // Deprecated.
				'axis'    => true, // Deprecated.
				'bgcolor' => true, // Deprecated.
				'char'    => true, // Deprecated.
				'charoff' => true, // Deprecated.
				'colspan' => true,
				'headers' => true,
				'rowspan' => true,
				'scope'   => true, // Deprecated.
				'valign'  => true, // Deprecated.
				'width'   => true, // Deprecated.
			)
		),    
		'tfoot'    => array_merge(
			prefix_allowed_global_attributes(),
			array(
				'align'   => true, // Deprecated.
				'bgcolor' => true, // Deprecated.
				'char'    => true, // Deprecated.
				'charoff' => true, // Deprecated.
				'valign'  => true, // Deprecated.
			)
		),
		'th'       => array_merge(
			prefix_allowed_global_attributes(),
			array(
				'abbr'    => true,
				'align'   => true, // Deprecated.
				'axis'    => true, // Deprecated.
				'bgcolor' => true, // Deprecated.
				'char'    => true, // Deprecated.
				'charoff' => true, // Deprecated.
				'colspan' => true,
				'headers' => true,
				'rowspan' => true,
				'scope'   => true,
				'valign'  => true, // Deprecated.
				'width'   => true, // Deprecated.
			)
		),
		'thead'    => array_merge(
			prefix_allowed_global_attributes(),
			array(
				'align'   => true, // Deprecated.
				'bgcolor' => true, // Deprecated.
				'char'    => true, // Deprecated.
				'charoff' => true, // Deprecated.
				'valign'  => true, // Deprecated.
			)
		),
		'tr'       => array_merge(
			prefix_allowed_global_attributes(),
			array(
				'align'   => true, // Deprecated.
				'bgcolor' => true, // Deprecated.
				'char'    => true, // Deprecated.
				'charoff' => true, // Deprecated.
				'valign'  => true, // Deprecated.
			)
		),
		'modele_tr'       => array_merge(
			prefix_allowed_global_attributes(),
			array(
				'align'   => true, // Deprecated.
				'bgcolor' => true, // Deprecated.
				'char'    => true, // Deprecated.
				'charoff' => true, // Deprecated.
				'valign'  => true, // Deprecated.
			)
		),

		// Forms.
		'button'   => array_merge(
			prefix_allowed_global_attributes(),
			array(
				'autofocus'      => true,
				'disabled'       => true,
				'form'           => true,
				'formaction'     => true,
				'formenctype'    => true,
				'formmethod'     => true,
				'formnovalidate' => true,
				'formtarget'     => true,
				'name'           => true,
				'type'           => true,
				'value'          => true,
			)
		),
		'datalist' => prefix_allowed_global_attributes(),
		'fieldset' => array_merge(
			prefix_allowed_global_attributes(),
			array(
				'disabled' => true,
				'form'     => true,
				'name'     => true,
			)
		),
		'form'     => array_merge(
			prefix_allowed_global_attributes(),
			array(
				'accept'         => true, // Deprecated.
				'accept-charset' => true,
				'action'         => true,
				'enctype'        => true,
				'method'         => true,
				'name'           => true,
				'novalidate'     => true,
				'target'         => true,
			)
		),
		'input'    => array_merge(
			prefix_allowed_global_attributes(),
			array(
				'accept'         => true,
				'alt'            => true,
				'autocomplete'   => true,
				'autofocus'      => true,
				'capture'        => true,
				'checked'        => true,
				'dirname'        => true,
				'disabled'       => true,
				'form'           => true,
				'formaction'     => true,
				'formenctype'    => true,
				'formmethod'     => true,
				'formnovalidate' => true,
				'formtarget'     => true,
				'height'         => true,
				'list'           => true,
				'max'            => true,
				'maxlength'      => true,
				'min'            => true,
				'minlength'      => true,
				'multiple'       => true,
				'name'           => true,
				'pattern'        => true,
				'placeholder'    => true,
				'readonly'       => true,
				'required'       => true,
				'size'           => true,
				'src'            => true,
				'step'           => true,
				'type'           => true,
				'value'          => true,
				'width'          => true,
			)
		),
		'label'    => array_merge(
			prefix_allowed_global_attributes(),
			array(
				'for'  => true,
				'form' => true, // Deprecated.
			)
		),
		'legend'   => prefix_allowed_global_attributes(),
		'meter'    => array_merge(
			prefix_allowed_global_attributes(),
			array(
				'form'    => true,
				'high'    => true,
				'low'     => true,
				'max'     => true,
				'min'     => true,
				'optimum' => true,
				'value'   => true,
			)
		),
		'optgroup' => array_merge(
			prefix_allowed_global_attributes(),
			array(
				'disabled' => true,
				'label' => true,
			)
		),
		'option'   => array_merge(
			prefix_allowed_global_attributes(),
			array(
				'disabled' => true,
				'label'    => true,
				'selected' => true,
				'value'    => true,
			)
		),
		'output'   => array_merge(
			prefix_allowed_global_attributes(),
			array(
				'for' => true,
			)
		),
		'progress' => array_merge(
			prefix_allowed_global_attributes(),
			array(
				'max'   => true,
				'value' => true,
			)
		),
		'select'   => array_merge(
			prefix_allowed_global_attributes(),
			array(
				'autofocus' => true,
				'disabled'  => true,
				'form'      => true,
				'multiple'  => true,
				'name'      => true,
				'required'  => true,
				'size'      => true,
			)
		),
		'textarea' => array_merge(
			prefix_allowed_global_attributes(),
			array(
				'autofocus'   => true,
				'cols'        => true,
				'disabled'    => true,
				'form'        => true,
				'maxlength'   => true,
				'minlength'   => true,
				'name'        => true,
				'placeholder' => true,
				'readonly'    => true,
				'required'    => true,
				'rows'        => true,
				'spellcheck'  => true,
				'wrap'        => true,
			)
		),

		// Interactive Elements.
		'details' => array_merge(
			prefix_allowed_global_attributes(),
			array(
				'open' => true,
			)
		),
		'dialog'  => array_merge(
			prefix_allowed_global_attributes(),
			array(
				'open' => true,
			)
		),
		'menu'    => array_merge(
			prefix_allowed_global_attributes(),
			array(
				'label' => true,
				'type' => true,
			)
		),
		'summary' => prefix_allowed_global_attributes(),

		// Web Components.
		'slot'     => array_merge(
			prefix_allowed_global_attributes(),
			array(
				'name' => true,
			)
		),
		'template' => prefix_allowed_global_attributes(),
	);
}

/**
 * Allowed Global Attributes.
 *
 * @return array
 */
function prefix_allowed_global_attributes() {
	return array(
		'aria-*'              => true,
		'accesskey'           => true,
		'autocapitalize'      => true,
		'autocomplete'        => true,
		'class'               => true,
		'contenteditable'     => true,
		'data-*'              => true,
		'dir'                 => true,
		'draggable'           => true,
		'dropzone'            => true,
		'exportparts'         => true,
		'hidden'              => true,
		'id'                  => true,
		'inputmode'           => true,
		'is'                  => true,
		'itemid'              => true,
		'intemprop'           => true,
		'itemref'             => true,
		'itemscope'           => true,
		'itemtype'            => true,
		'lang'                => true,
		'part'                => true,
		'slot'                => true,
		'spellcheck'          => true,
		'style'               => true,
		'tabindex'            => true,
		'title'               => true,
		'translate'           => true,
		'onabort'             => true,
		'onautocomplete'      => true,
		'onautocompleteerror' => true,
		'onblur'              => true,
		'oncancel'            => true,
		'oncanplay'           => true,
		'oncanplaythrough'    => true,
		'onchange'            => true,
		'onclick'             => true,
		'onclose'             => true,
		'oncontextmenu'       => true,
		'oncuechange'         => true,
		'ondblclick'          => true,
		'ondrag'              => true,
		'ondragend'           => true,
		'ondragenter'         => true,
		'ondragexit'          => true,
		'ondragleave'         => true,
		'ondragover'          => true,
		'ondragstart'         => true,
		'ondrop'              => true,
		'ondurationchange'    => true,
		'onemptied'           => true,
		'onended'             => true,
		'onerror'             => true,
		'onfocus'             => true,
		'oninput'             => true,
		'oninvalid'           => true,
		'onkeydown'           => true,
		'onkeypress'          => true,
		'onkeyup'             => true,
		'onload'              => true,
		'onloadeddata'        => true,
		'onloadedmetadata'    => true,
		'onloadstart'         => true,
		'onmousedown'         => true,
		'onmouseenter'        => true,
		'onmouseleave'        => true,
		'onmousemove'         => true,
		'onmouseout'          => true,
		'onmouseover'         => true,
		'onmouseup'           => true,
		'onmousewheel'        => true,
		'onpause'             => true,
		'onplay'              => true,
		'onplaying'           => true,
		'onprogress'          => true,
		'onratechange'        => true,
		'onreset'             => true,
		'onresize'            => true,
		'onscroll'            => true,
		'onseeked'            => true,
		'onseeking'           => true,
		'onselect'            => true,
		'onshow'              => true,
		'onsort'              => true,
		'onstalled'           => true,
		'onsubmit'            => true,
		'onsuspend'           => true,
		'ontimeupdate'        => true,
		'ontoggle'            => true,
		'onvolumechange'      => true,
		'onwaiting'           => true,
	);
}

?>