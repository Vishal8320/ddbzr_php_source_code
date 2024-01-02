'use strict';

function autosize() {
	// auto adjust the height of
	jQuery('body').on('keyup', 'textarea', function() {
		jQuery(this).height(0);
		jQuery(this).height((this.scrollHeight-10));
	});
}

		
		function dropdownMenu(type) {
			// 1: Reset the menu
			if(type) {
				jQuery('.menu-image').removeClass('menu-image-active');
				jQuery('#menu-dd-container').hide();
			} else {
				// Dropdown Menu Icon
				jQuery('.menu-image').on("click", function() {
					jQuery('.menu-image').toggleClass('menu-image-active');
					jQuery('#menu-dd-container').toggle();
					// showNotification('close', 1);
				});
			}
		}
		jQuery(document).mouseup(function(e) {
			// All the divs that needs to be excepted when being clicked (including the buttons itselfs)
			var container = jQuery('.menu-image');
	
			// If the element clicked isn't the container nor a descendant then hide the menus, dropdowns
			if (!container.is(e.target) && container.has(e.target).length === 0) {
				dropdownMenu(1);
				
			}
		});
		

		
		 // Admin Settings
	// 	 $(document).ready(function() {
	// 	 jQuery('.edit-menu-item').click(function() {
			
	// 		jQuery(this).addClass('edit-menu-item-active').siblings().removeClass('edit-menu-item-active');
	// 		jQuery('.edit-general,.edit-limits,.edit-emails').hide();
	// 		jQuery('.'+jQuery(this).attr('id')).show();
	// 	});
	// });
	// function changeTab(tab) {
	// 	$(tab).addClass('edit-menu-item-active').siblings().removeClass('edit-menu-item-active');
	// 	var tabId = $(tab).attr('id');
	// 	$('.edit-section').hide();
	// 	$('.' + tabId).show();
	//   }



	
	function changeTab(current, tabIdToShow,tabIdsToHide ) {
		// Hide all tab content except the selected one
		var itemId = $(current).attr("id");
		$('.edit-menu-item').removeClass('edit-menu-item-active');
		$('#' + itemId).addClass('edit-menu-item-active');
	
		// Show all other tabs except the ones specified in the array
		for (var i = 0; i < tabIdsToHide.length; i++) {
			$(tabIdsToHide[i]).hide();
		}
		
		// Show the selected tab
		$(tabIdToShow).css('display', 'block');
		
	}
	
	
	
	
	  
	  
		// 



function sidebarShow(id) {
	jQuery('#show-more-btn-'+id).remove();
	if(id == 1) {
		jQuery('.sidebar-events').fadeIn(300);
	} else if(id == 2) {
		jQuery('.sidebar-dates').fadeIn(300);
	} else if(id == 3) {
		jQuery('.sidebar-group').fadeIn(300);
	} else if(id == 4) {
		jQuery('.sidebar-page').fadeIn(300);
	}
}
function adminSubMenu(id) {
	jQuery('#sub-menu'+id).toggleClass('sidebar-link-sub-active');
	jQuery('#sub-menu-content'+id).slideToggle(300);
}
function checkAlert() {
	if(!document.hasFocus()) {
		// If the current document title doesn't have a count alert, add one
		var title = document.title;
		if(title.charAt(0) !== "(") {
			if(totalNotifications > 0) {
				document.title = "(" + totalNotifications + ") " + document.title;
			} else {
				document.title = "(!) " + document.title;
			}
		}
		notificationTitle(2);
	}
}

jQuery(document).ready(function() {
	if(typeof friends_windows === 'undefined') {
		window.friends_windows = [];
	}
	dropdownMenu();
});

// Here some functions made by me.

$(document).on("click",'.modal-overlay-cal',function(event) {
	if (event.target === this) {
	$(this).hide();
	}
  });
  function remove_items_cal(id){
	$(id).hide();
  }
//   class ButtonSwitch {
// 	constructor(domNode) {
// 	  this.switchNode = domNode;
// 	  this.switchNode.addEventListener('click', () => this.toggleStatus());
  
// 	  // Set background color for the SVG container Rect
// 	  var color = getComputedStyle(this.switchNode).getPropertyValue(
// 		'background-color'
// 	  );
// 	  var containerNode = this.switchNode.querySelector('rect.container');
// 	  containerNode.setAttribute('fill', color);
// 	}
  
// 	// Switch state of a switch
// 	toggleStatus() {
// 	  const currentState =
// 		this.switchNode.getAttribute('aria-checked') === 'true';
// 	  const newState = String(!currentState);
  
// 	  this.switchNode.setAttribute('aria-checked', newState);
// 	}
//   }


  // Switch state of a switch

  
// function ButtonSwitch(domNode) {
// 	this.switchNode = domNode;
// 	this.isSwitched = false; // Track the switch state
  
// 	this.handleClick = () => {
// 	  if (!this.isSwitched) {
// 		this.toggleStatus();
// 		this.isSwitched = true;
// 	  }
// 	};
  
// 	this.switchNode.addEventListener('click', this.handleClick);
  
// 	// Set background color for the SVG container Rect
// 	var color = getComputedStyle(this.switchNode).getPropertyValue('background-color');
// 	var containerNode = this.switchNode.querySelector('rect.container');
// 	containerNode.setAttribute('fill', color);
//   }
  
//   // Switch state of a switch
//   ButtonSwitch.prototype.toggleStatus = function () {
// 	const currentState = this.switchNode.getAttribute('aria-checked') === 'true';
// 	const newState = String(!currentState);
// 	this.switchNode.setAttribute('aria-checked', newState);
//   };
function ButtonSwitch(domNode) {
	this.switchNode = domNode;
	this.isSwitched = false; // Track the switch state
  
	this.handleClick = () => {
	  if (!this.isSwitched) {
		this.toggleStatus();
		this.isSwitched = true;
	  }
	};
  
	this.switchNode.addEventListener('click', this.handleClick);
	this.updateBackgroundColor();
  }
  
  // Switch state of a switch
  ButtonSwitch.prototype.toggleStatus = function () {
	const currentState = this.switchNode.getAttribute('aria-checked') === 'true';
	const newState = String(!currentState);
	this.switchNode.setAttribute('aria-checked', newState);
	this.updateBackgroundColor();
  };
  
  // Update background color based on the switch state
  ButtonSwitch.prototype.updateBackgroundColor = function () {
	var containerNode = this.switchNode.querySelector('rect.container');
	var currentState = this.switchNode.getAttribute('aria-checked') === 'true';
  
	if (currentState) {
	  var color = getComputedStyle(this.switchNode).getPropertyValue('background-color');
	  containerNode.setAttribute('fill', color);
	} else {
	  containerNode.removeAttribute('fill');
	}
  };
  

  
  // Call the toggleButtonSwitch function when the button is clicked
  function toggleButtonSwitch(id) {
	var button = document.getElementById(id);
	new ButtonSwitch(button);
  }
  
  
  
  
  // Initialize switches
  window.addEventListener('load', function () {
	// Initialize the Switch component on all matching DOM nodes
	Array.from(document.querySelectorAll('button[role^=switch]')).forEach(
	  (element) => new ButtonSwitch(element)
	);
  });



  // loading bar code

  jQuery(function() {
	jQuery("body").on("click", "a[rel='loadpage']", function(e) {
		
		// Get the link location that was clicked
		liveLoad(jQuery(this).attr('href'), 0, null);
		
		return false;
	});
});


// Override the back button to get the ajax content via the back content */
jQuery(window).on('popstate', function(ev) {
	liveLoad(location.href, 0, null);
});

$.fn.scrollIntoView = function(padding, duration, easing) {	
    jQuery('html,body').animate({
        scrollTop: this.offset().top-padding
    }, duration, easing);
    return this;
};
function startLoadingBar() {
	// only add progress bar if added yet.
	jQuery("#loading-bar").show();
	jQuery("#loading-bar").width((50 + Math.random() * 30) + "%");
}
function stopLoadingBar() {
	//End loading animation
	jQuery("#loading-bar").width("101%").delay(200).fadeOut(400, function() {
		jQuery(this).width("0");
	});
}
function liveLoad(pageurl, type, parameters) {
	// page url = request url
	// type = 1: POST; 0: GET;
	// parameters: serialized params
	
	
	startLoadingBar();
	
	if(type == 1) {
		var type = "POST";
	} else {
		var type = "GET";
	}
	
	// Request the page
	$.ajax({url: pageurl, type: type, data: parameters, success: function(data) {
		var result = jQuery.parseJSON(data);
		// Show the content
		jQuery('#content').html(result.content);
		// Stop the loading bar
		stopLoadingBar();
		// Set the new title tag
		document.title = result.title;
		// Scroll the document at the top of the page
		jQuery(document).scrollTop(0);
		
	}});
	
	// Store the url to the last page accessed
	if(pageurl != window.location) {
		window.history.pushState({path:pageurl}, '', pageurl);	
	}
	return false;
}
// get value from cookies
function getCookieValue(cookieName) {
	let name = cookieName + "=";
	let decodedCookie = decodeURIComponent(document.cookie);
	let cookieArray = decodedCookie.split(';');
  
	for (let i = 0; i < cookieArray.length; i++) {
	  let cookie = cookieArray[i].trim();
	  if (cookie.startsWith(name)) {
		return cookie.substring(name.length);
	  }
	}
  
	return null;
  }
  // Function to check if local storage is supported and get permission
function checkLocalStoragePermission() {
	try {
	  localStorage.setItem('test', 'test');
	  localStorage.removeItem('test');
	  return true;
	} catch (e) {
	  return false;
	}
  }

// Function to store data in local storage
function put_into_local(storage_key_name, search_from, search_by, search_val) {
	// Check if local storage permission is available
	if (!checkLocalStoragePermission()) {
	  // If permission not available, ask the user for permission
	  if (confirm("This website would like to store data in your local storage.")) {
		localStorage.setItem('localStoragePermission', 'allowed');
	  } else {
		alert("Local storage permission denied. Some features may not work correctly.");
		return;
	  }
	}
  
	// Check if the data already exists in local storage
	let existingData = get_from_local(storage_key_name, search_from, search_by, search_val);
	if (existingData) {
	//   console.log('Data already exists for key:', storage_key_name);
	  return;
	}
  
	// Create a new object to store the parameters
	let data = {
	  search_from: search_from,
	  search_by: search_by,
	  search_val: search_val
	};
  
	// Set a timestamp property in the data to track creation time
	data.timestamp = Date.now();
  
	// Convert the data object to a JSON string
	let newDataString = JSON.stringify(data);
  
	// Calculate the size of the new data in bytes
	let newDataSize = newDataString.length;
  
	// Set a maximum size for local storage (example: 20 MB)
	let maxStorageSize = 20 * 1024 * 1024; // 20 MB
  
	// Get the current size of local storage data
	let currentSize = JSON.stringify(localStorage).length;
  
	// If adding new data exceeds the limit, prune the data (LRU approach)
	while (currentSize + newDataSize > maxStorageSize) {
	  let oldestKey = getOldestDataKey(); // Get the key of the oldest data
	  if (oldestKey) {
		currentSize -= localStorage.getItem(oldestKey).length + oldestKey.length; // Update currentSize
		localStorage.removeItem(oldestKey); // Remove the oldest data
	  } else {
		break; // Break if no data is found to avoid infinite loop
	  }
	}
  
	// Store the JSON string in local storage using the specified key (storage_key_name)
	localStorage.setItem(storage_key_name, newDataString);
  }
  
  // Function to get the key of the oldest data in local storage
  function getOldestDataKey() {
	let oldestKey = null;
	let oldestTimestamp = Infinity;
  
	for (let i = 0; i < localStorage.length; i++) {
	  let key = localStorage.key(i);
	  let dataString = localStorage.getItem(key);
	  let data = JSON.parse(dataString);
	  if (data && data.timestamp < oldestTimestamp) {
		oldestKey = key;
		oldestTimestamp = data.timestamp;
	  }
	}
  
	return oldestKey;
  }
  
  
// Function to get the data from local storage based on provided parameters
function get_from_local(storage_key_name, search_from, search_by, search_val) {
	// Get the data from local storage based on the specified key (storage_key_name)
	let jsonString = localStorage.getItem(storage_key_name);
  
	// If data exists in local storage for the specified key
	if (jsonString) {
	  // Parse the data from JSON string
	  let storedData = JSON.parse(jsonString);
  
	  // Check if the stored data matches the provided parameters
	  if (
		storedData.search_from === search_from &&
		storedData.search_by === search_by &&
		storedData.search_val === search_val
	  ) {
		return storedData;
	  }
	}
  
	// If data doesn't exist for the specified key or doesn't match the parameters, return null
	return null;
  }
//   function bulk_from_local(storage_key_name, search_from, search_by) {
// 	let matchingValues = [];
  
// 	// Convert the search_by parameter to a string if it's not null or undefined
// 	search_by = typeof search_by === "string" ? search_by.toLowerCase() : "";
  
// 	// Iterate through all items in local storage
// 	for (let i = 0; i < localStorage.length; i++) {
// 	  let key = localStorage.key(i);
// 	  let dataString = localStorage.getItem(key);
  
// 	  // Parse the data from JSON string
// 	  let data = JSON.parse(dataString);
  
// 	  // Check if data matches the provided search parameters and the storage_key_name
// 	  if (
// 		data &&
// 		(!search_from || (typeof data.search_from === "string" && data.search_from.toLowerCase().includes(search_from.toLowerCase()))) &&
// 		(!search_by || (typeof data.search_by === "string" && data.search_by.toLowerCase().includes(search_by))) &&
// 		key.toLowerCase().includes(storage_key_name.toLowerCase())
// 	  ) {
// 		matchingValues.push(data);
// 	  }
  
// 	  // Check if we've reached the limit of 3 matching values
// 	  if (matchingValues.length >= 3) {
// 		break;
// 	  }
// 	}
  
// 	return matchingValues;
//   }


  function bulk_from_local(storage_key_name, search_from, search_by) {
	let matchingValues = [];
  
	search_by = typeof search_by === "string" ? search_by.toLowerCase() : "";
  
	for (let i = 0; i < localStorage.length; i++) {
	  let key = localStorage.key(i);
	  let dataString = localStorage.getItem(key);
  
	  try {
		let data = JSON.parse(dataString);
  
		if (
		  data &&
		  (!search_from || (typeof data.search_from === "string" && data.search_from.toLowerCase().includes(search_from.toLowerCase()))) &&
		  (!search_by || (typeof data.search_by === "string" && data.search_by.toLowerCase().includes(search_by))) &&
		  key.toLowerCase().includes(storage_key_name.toLowerCase())
		) {
		  matchingValues.push(data);
		}
  
		if (matchingValues.length >= 3) {
		  break;
		}
	  } catch (error) {
		console.error(`Error parsing data for key '${key}':`, error);
		// Handle the error gracefully, such as skipping this entry.
	  }
	}
  
	return matchingValues;
  }
  
  
  function checkInputType(inputValue) {
	// Check if the input value contains only digits (numeric)
	if (/^\d+$/.test(inputValue)) {
	  return 'p_num';
	}
  
	// Check if the input value contains only alphabets, digits, or underscores (string)
	if (/^[a-zA-Z0-9_]+$/.test(inputValue)) {
	  return 'uname';
	}
  
	// If not containing only digits or alphabets, digits, or underscores, consider it as "other"
	return 'other';
  
   } 
   function get_suggestion(key_name,print_id, search_by, search_val_id) {
	// if outside of element clicked suggestion will be hide
	let $searchHistory = $(print_id);
	$(document).on('click', function(event) {
		
		// Check if the clicked element is #search_history or one of its descendants
		if (!$searchHistory.is(event.target) && $searchHistory.has(event.target).length === 0) {
		  // Clicked outside the search history element, clear it
		  $searchHistory.html('');
		}
	  });

    
	// Get the value from the HTML input field with the provided id and trim it
	let inputVal = $(search_val_id).val();
  
	// Create the storage key by concatenating 'search_' with the trimmed search_val
	let storage_key = key_name + inputVal;
	let search_from = checkInputType(inputVal);  // checkInputType return value p_num,uname (if suggestion set only uname type uname here)
	// Get data from local storage based on provided parameters
	let storedDataArray = bulk_from_local(storage_key, search_from, search_by);
      
	// Create the HTML content based on storedDataArray
	let htmlContent = '';

	storedDataArray.forEach((data, index) => {
	  let className = 'row_' + ((index % 2) + 1); // To alternate between row_1 and row_2
  
	  // Use the replace method to add <b> tags around the matching part of the string
	  let highlightedVal = data.search_val.replace(new RegExp(inputVal, 'gi'), (match) => `<b>${match}</b>`);
	  htmlContent += `<div class="${className}" id="history_${data.search_val}" data="${data.search_val}" onclick="fill_search_suggestion(this,'#${search_val_id.id}','#${$searchHistory[0].getAttribute('id')}')">${highlightedVal}</div>`;
	});
  
	// Create the complete HTML structure and set it to the print_id element
	let completeHtml = `<div class="search-locality">
						  <div class="notification-inner">
							<div id="locality_data">${htmlContent}</div>
						  </div>
						</div>`;
  
	// Set the complete HTML structure to the print_id element
	$(print_id).html(completeHtml);
  
	if (storedDataArray.length > 0) {
	  return true;
	} else {
	  // Clear the input field if data not found in local storage or doesn't match
	  $(print_id).html('');
	  return false;
	}
	
  }
	
  /** 
   
 code if hide any dialog box 

  // Set a flag to keep track of click events
  let clickedInside = false;

  // Add a click event listener to the document
  $(document).on('click', function(event) {
    let $searchHistory = $('#search_history');

    // Check if the clicked element is #search_history or one of its descendants
    if ($searchHistory.is(event.target) || $searchHistory.has(event.target).length > 0) {
      // Clicked inside the search history element
      clickedInside = true;
    } else {
      // Clicked outside the search history element
      if (!clickedInside) {
        $searchHistory.html('');
      }
      clickedInside = false;
    }
  });
 
 
     * */


  function fill_search_suggestion(click_id, fill_id, remove_id) {
	let value = $(click_id).attr("data");
	$(fill_id).val(value); // Set the value in the target element
	$(remove_id).html('');
  }
  
  
  
  
async function getDeviceInfo() {
	const deviceInfo = {
	  DEVICE_TYPE: '',
	  DEVICE_SCREEN_RESOLUTION: '',
	  RAM: '',
	  STORAGE_PERMISSION: '',
	  CAMERA_PERMISSION: '',
	  GRAPHICS_CARD: '',
	  LANGUAGE: [],
	  VIEWPORT_WIDTH: '',
	  BROWSER_INFO: {},
	  TIME_ZONE: '',
	  DEVICE_TIME: '',
	  DEVICE_MOUSE: '',
	  DEVICE_TOUCH_SCREEN: '',
	  LATITUDE: '',
	  LONGITUDE: '',
	  LOCATION_DATA: {},
	};
  
	// Device Type
	deviceInfo.DEVICE_TYPE = /Mobi|Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)
	  ? 'Mobile'
	  : /iPad|Android|webOS/i.test(navigator.userAgent)
	  ? 'Tablet'
	  : 'Desktop';
  
	// Device Screen Resolution
	deviceInfo.DEVICE_SCREEN_RESOLUTION = `${screen.width}x${screen.height}`;
  
	// RAM
	deviceInfo.RAM = navigator.deviceMemory || 'N/A';
  
	// Storage Permission (Assuming permissions granted if estimate() resolves without error)
	try {
	  const storageEstimation = await navigator.storage.estimate();
	  deviceInfo.STORAGE_PERMISSION = storageEstimation.quota ? 'YES' : 'NO';
	} catch (error) {
	  deviceInfo.STORAGE_PERMISSION = 'N/A';
	}
  
	// Camera Permission (Assuming permissions granted if enumerateDevices() resolves without error)
	try {
	  const cameraDevices = await navigator.mediaDevices.enumerateDevices();
	  const hasCamera = cameraDevices.some((device) => device.kind === 'videoinput');
	  deviceInfo.CAMERA_PERMISSION = hasCamera ? 'YES' : 'NO';
	} catch (error) {
	  deviceInfo.CAMERA_PERMISSION = 'N/A';
	}
  
	// Graphics Card (Assuming YES if WebGL is supported)
	deviceInfo.GRAPHICS_CARD = !!window.WebGLRenderingContext ? 'YES' : 'NO';
  
	// Language
	deviceInfo.LANGUAGE = navigator.languages || [navigator.language || ''];
  
	// Viewport Width
	deviceInfo.VIEWPORT_WIDTH = window.innerWidth;
  
	// Browser Info
	deviceInfo.BROWSER_INFO = {
	  name: navigator.userAgent,
	  version: navigator.appVersion,
	  platform: navigator.platform,
	  userAgent: navigator.userAgent,
	};
  
	// Time Zone
	deviceInfo.TIME_ZONE = Intl.DateTimeFormat().resolvedOptions().timeZone;
  
	// Device Time
	deviceInfo.DEVICE_TIME = new Date().toString();
  
	// Device Mouse
	deviceInfo.DEVICE_MOUSE = 'ontouchstart' in window || navigator.msMaxTouchPoints > 0 ? 'NO' : 'YES';
  
	// Device Touch Screen
	deviceInfo.DEVICE_TOUCH_SCREEN = 'ontouchstart' in window || navigator.msMaxTouchPoints > 0 ? 'YES' : 'NO';
  
	// Latitude and Longitude
	try {
	  const position = await new Promise((resolve, reject) => {
		navigator.geolocation.getCurrentPosition(resolve, reject);
	  });
	  deviceInfo.LATITUDE = position.coords.latitude;
	  deviceInfo.LONGITUDE = position.coords.longitude;
	} catch (error) {
	  deviceInfo.LATITUDE = 'N/A';
	  deviceInfo.LONGITUDE = 'N/A';
	}
  
	// Location Data
	// try {
	//   const location = await reverseGeocode(deviceInfo.LATITUDE, deviceInfo.LONGITUDE);
	//   deviceInfo.LOCATION_DATA = location;
	// } catch (error) {
	//   deviceInfo.LOCATION_DATA = {};
	// }
  
	return deviceInfo;
  }

function getLocation() {
	return new Promise((resolve, reject) => {
	  if (navigator.geolocation) {
		navigator.geolocation.getCurrentPosition(
		  (position) => {
			const latitude = position.coords.latitude;
			const longitude = position.coords.longitude;
			resolve({ latitude, longitude });
		  },
		  (error) => {
			reject('Error fetching geolocation: ' + error.message);
		  }
		);
	  } else {
		reject('Geolocation is not supported by this browser.');
	  }
	});
  }
  
  async function reverseGeocode(latitude, longitude) {
    const url = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${latitude}&lon=${longitude}`;
    try {
        const response = await fetch(url);

        if (!response.ok) {
            throw new Error("Network response was not ok");
        }

        const data = await response.json();

        if (!data.address) {
            alert("Data not found");
            return false;
        }

        return data.address;
    } catch (error) {
        alert("An error occurred: Geocode", error);
        console.log("An error occurred:", error);
        return false;
    }
}

  
  async function getUserInfo() {
	try {
	  const position = await getLocation();
	  const location = await reverseGeocode(position.latitude, position.longitude);
  
	  const info = {
		latitude: position.latitude,
		longitude: position.longitude,
		location: location,
		
		// ... add other properties as needed
	  };
  
	  return info;
	} catch (error) {
	  console.error(error);
	  return null;
	}
  }
  
  function askForLocationPermission() {
	function showAlertAndRefresh() {
	  alert("Please enable location permissions to continue.");
	  location.reload();
	}
  
	function requestLocationPermission() {
	  if ('permissions' in navigator) {
		navigator.permissions.query({ name: 'geolocation' }).then(function(permissionStatus) {
		  if (permissionStatus.state === 'denied') {
			showAlertAndRefresh();
		  } else if (permissionStatus.state === 'prompt') {
			permissionStatus.onchange = function() {
			  if (this.state === 'denied') {
				showAlertAndRefresh();
			  } else if (this.state === 'granted') {
				// Location permission granted, you can proceed with getting the location here
			  }
			};
		  } else if (permissionStatus.state === 'granted') {
			// Location permission already granted, you can proceed with getting the location here
		  }
		});
	  } else if ('geolocation' in navigator) {
		navigator.geolocation.getCurrentPosition(function(position) {
		  // Location permission is granted, you can proceed with getting the location here
		}, function(error) {
		  showAlertAndRefresh();
		});
	  } else {
		showAlertAndRefresh();
	  }
	}
  
	// Request location permission initially
	requestLocationPermission();
  }

  askForLocationPermission();
  
  
  

	