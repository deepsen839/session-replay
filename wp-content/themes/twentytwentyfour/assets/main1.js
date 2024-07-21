let events = [];

// const downloadToFile = (content, filename, contentType) => {
//     const a = document.createElement('a');
//     const file = new Blob([content], { type: contentType });
  
//     a.href = URL.createObjectURL(file);
//     a.download = filename;
//     a.click();
  
//     URL.revokeObjectURL(a.href);
//   };


  fetch("wp-content/themes/twentytwentyfour/assets/my-new-file.txt")
  .then((res) => {return res.json()})
  .then((text) => {
    
    let events3 = text
    events3.forEach(function (arrayItem) {
        
        events.push(arrayItem)
    });
    console.log(events)
    new rrwebPlayer({
        target: document.body,
        props:{
            events
        }
    })
   })
  .catch((e) => console.error(e));

// setTimeout(function(){

//     rrweb.record({
//         emit(event) {
//           // push event into the events array
          
//           events.push(event);
      
//         //   console.log(JSON.stringify( {events} ))
          
//         },
//         checkoutEveryNms:10
//       });



// },10000)

// setTimeout(() => {
//         new rrwebPlayer({
//         target: document.body,
//         props:{
//             events
//         }
//     })
//     console.log(JSON.stringify(events))
// }, 15000);


// setInterval(function() {downloadToFile({ events }, 'my-new-file.txt', 'text/plain')},5000);