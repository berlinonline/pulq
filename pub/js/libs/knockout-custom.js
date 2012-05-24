// ############################################
// #     knockoutjs observable extensions     #
// ############################################
ko.observableArray.fn.indexed = function() { // adds an $index prop to array items to have $index available inside template "foreach" bindings
   //whenever the array changes, make one loop to update the index on each
   this.subscribe(function(newValue) {
       if (newValue) {
           var item;
           for (var i = 0, j = newValue.length; i < j; i++) {
               item = newValue[i];
               if (!ko.isObservable(item.$index)) {
                  item.$index = ko.observable();
               }
               item.$index(i);      
           }
       }   
   }); 
   this.valueHasMutated(); 
   return this;
};