// import * as Handlebars from "./handlebars-v4.0.11";
// owncloud does not need the above import

(function (OC, window, $, undefined) {
	'use strict';

	$(document).ready(function () {

		let translations = {
			newNote: $('#new-note-string').text()
		};

// this notes object holds all our notes
		/**
		 * JS's way of defining a constructor :(
		 * @param baseUrl
		 * @constructor
		 */
		let Notes = function (baseUrl) {
			this._baseUrl = baseUrl;
			this._notes = [];
			this._activeNote = undefined;
		};

		/**
		 * JS's way of defining/adding/modifying field/method instances for a 'class' (all JS objects have a prototype, aka, 'template class' predefined).
		 *
		 * All JavaScript objects inherit properties and methods from a prototype.

		 Date objects inherit from Date.prototype. Array objects inherit from Array.prototype. Person objects inherit from Person.prototype.

		 The Object.prototype is on the top of the prototype inheritance chain:

		 Date objects, Array objects, and Person objects inherit from Object.prototype. Our Notes.prototype is not an exception :)
		 *
		 * @type {{load: load, getActive: (function(): *), removeActive: (function(): *), create: (function(*=): *), getAll: (function(): (Array|*)), loadAll: (function(): *), updateActive: (function(*, *): (void|*))}}
		 */
		Notes.prototype = {
			load: function (id) {
				console.log("DEBUGGING IN script->Notes->load : attempt to load note id : " + id);
				let self = this;
				this._notes.forEach(function (note) {
					if (note.id === id) {
						note.active = true;
						self._activeNote = note;
					} else {
						note.active = false;
					}
				});
				console.log("DEBUGGING IN script->Notes->load : active note id : " + self._activeNote.id);
			},
			getActive: function () {
				return this._activeNote;
			},
			removeActive: function () {
				let index;
				/**
				 * jQuery.Deferred( [beforeStart ] )Returns: Deferred
				 Description: A factory function that returns a chainable utility object with methods to register multiple callbacks into callback queues, invoke callback queues, and relay the success or failure state of any synchronous or asynchronous function.

				 version added: 1.5
				 */
				let deferred = $.Deferred();
				let id = this._activeNote.id;
				this._notes.forEach(function (note, counter) {
					if (note.id === id) {
						index = counter;
					}
				});

				if (index !== undefined) {
					// delete cached active note if necessary
					if (this._activeNote === this._notes[index]) {
						// The JavaScript delete operator removes a property from an object; if no more references to the same property are held, it is eventually released automatically.
						delete this._activeNote;
					}

					this._notes.splice(index, 1);

					$.ajax({
						url: this._baseUrl + '/' + id,
						method: 'DELETE'
					}).done(function () {
						deferred.resolve();
					}).fail(function () {
						deferred.reject();
					});
				} else {
					deferred.reject();
				}
				return deferred.promise();
			},
			create: function (note) {
				console.log(JSON.stringify(note));
				let deferred = $.Deferred();
				let self = this;
				$.ajax({
					url: this._baseUrl,
					method: 'POST',
					contentType: 'application/json',
					data: JSON.stringify(note)
				}).done(function (note) {
					self._notes.push(note);

					if (typeof self._notes === 'undefined') {
						console.log("DEBUG script.js : Notes.create() => _notes : undefined");
					} else {
						console.log("DEBUG script.js : Notes.create() => _notes : " + self._notes);
					}

					self._activeNote = note;
					self.load(note.id);
					deferred.resolve();
				}).fail(function () {
					deferred.reject();
				});
				return deferred.promise();
			},
			getAll: function () {
				if (typeof this._notes === 'undefined') {
					console.log("DEBUG script.js : Notes.getAll() => _notes : undefined");
				} else {
					console.log("DEBUG script.js : Notes.getAll() => _notes : " + this._notes);
				}

				return this._notes;
			},
			loadAll: function () {
				let deferred = $.Deferred();
				let self = this;
				$.get(this._baseUrl).done(function (notes) {
					self._activeNote = undefined;
					self._notes = notes;
					deferred.resolve();
				}).fail(function () {
					deferred.reject();
				});
				return deferred.promise();
			},
			updateActive: function (title, content) {
				let note = this.getActive();

				console.log("note : " + note.toString());

				note.title = title;
				note.content = content;

				console.log("note id : " + note.id);
				console.log("note title : " + note.title);
				console.log("note content : " + note.content);

				return $.ajax({
					url: this._baseUrl + '/' + note.id,
					method: 'PUT',
					contentType: 'application/json',
					data: JSON.stringify(note)
				});
			}
		};

// this will be the view that is used to update the html
		let View = function (notes) {
			this._notes = notes;
			console.log(notes.toString());
		};

		View.prototype = {
			renderContent: function () {
				let source = $('#content-tpl').html();
				let template = Handlebars.compile(source);

				console.log("DEBUGGING IN script->View->renderContent : get active node id : " + this._notes.getActive().id);
				console.log("DEBUGGING IN script->View->renderContent : get active node title : " + this._notes.getActive().title);
				console.log("DEBUGGING IN script->View->renderContent : get active node content : " + this._notes.getActive().content);
				let html = template({note: this._notes.getActive()});

				$('#editor').html(html);

				// handle saves
				let textarea = $('#note_content');
				let self = this;
				$('#save_btn').on("click", function () {
					let content = textarea.val();
					let title = content.split('\n')[0]; // first line is the title

					console.log("title : " + title);
					console.log("content : " + content);

					self._notes.updateActive(title, content).done(function () {
						console.log("Note updated");
						self.render();
					}).fail(function () {
						alert('Could not update note, not found');
					});
				});
			},
			renderNavigation: function () {
				let source = $('#navigation-tpl').html();
				let template = Handlebars.compile(source);
				let html = template({notes: this._notes.getAll()});

				$('#app-navigation ul').html(html);

				// create a new note
				let self = this;
				$('#new-note').on('click', function () {
					let note = {
						title: translations.newNote,
						content: ''
					};

					self._notes.create(note).done(function() {
						self.render();
						$('#editor textarea').focus();
					}).fail(function () {
						alert('Could not create note');
					});
				});

				// show app menu
				$('#app-navigation .app-navigation-entry-utils-menu-button').on('click', function () {
					let entry = $(this).closest('.note');
					entry.find('.app-navigation-entry-menu').toggleClass('open');
				});

				// delete a note
				$('#app-navigation .note .delete').on('click', function () {
					let entry = $(this).closest('.note');
					entry.find('.app-navigation-entry-menu').removeClass('open');

					self._notes.removeActive().done(function () {
						self.render();
					}).fail(function () {
						alert('Could not delete note, not found');
					});
				});

				// load a note
				$('#app-navigation .note > a').on('click', function () {
					let id = parseInt($(this).parent().data('id'), 10);
					console.log("DEBUGGING IN View->renderNavigation // load a note : selected note id : " + id);
					self._notes.load(id);
					self.render();
					$('#editor textarea').focus();
				});
			},
			render: function () {
				console.log("View re-rendering");
				this.renderNavigation();
				this.renderContent();
			}
		};

		let notes = new Notes(OC.generateUrl('/apps/ownnotes/notes'));
		let view = new View(notes);
		notes.loadAll().done(function () {
			view.render();
		}).fail(function () {
			alert('Could not load notes');
		});


	});

})(OC, window, jQuery);