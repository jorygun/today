
function startRotation(secs){
	var intID = setInterval(rotateDivs,secs*1000);
}
function rotateDivs(){
/* function grabs all the division with id starting with "page".
	They should (or all but one) initially be set as display:none.
	The script will run through turning on one div after the other
	and then repeat.

	vars are static (function.name) so they are preserverd across runs.

*/

/* Initialize */

	if ( typeof rotateDivs.dlist == 'undefined' ) {
		rotateDivs.dlist = document.querySelectorAll('div[id^="page"]');
		rotateDivs.dsize = rotateDivs.dlist.length - 1;
		for (let i = 0; i < rotateDivs.dsize; i++) {
			rotateDivs.dlist[i].style.display='none';
		}
		 rotateDivs.pointer = 0;
      rotateDivs.last = rotateDivs.dsize;
//       alert ("Initialized. " + rotateDivs.dsize + " divs" );
	}


    offdiv = rotateDivs.dlist[rotateDivs.last];
    	offdiv.style.display='none';

	ondiv = rotateDivs.dlist[rotateDivs.pointer];
		ondiv.style.display = 'block';

	++rotateDivs.pointer;
	rotateDivs.last = rotateDivs.pointer -1;

	if (rotateDivs.pointer > rotateDivs.dsize){
		rotateDivs.pointer = 0;
		rotateDivs.last = rotateDivs.dsize;
	}
	if (rotateDivs.pointer == 1){
		rotateDivs.last = 0;
	}
// alert ("pointer " + rotateDivs.pointer + " last " +rotateDivs.last );
return true;
}

