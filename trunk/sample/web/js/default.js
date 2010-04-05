//return object if passed ID or object - allows for easier use of references on page
function returnObject(objectReference) {
	if (typeof(objectReference) == "string") {
		var foundObject = document.getElementById(objectReference);
		if (foundObject != null) {
			if (foundObject.id == objectReference) {
				return foundObject;
			} else {
				return null;
			}
		} else {
			return null;
		}
	} else if (typeof(objectReference) == "object") {
		return objectReference;
	}
	return null;
}