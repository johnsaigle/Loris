var CandidateInfo = React.createClass({
    displayName: "CandidateInfo",


    getInitialState: function () {
        return {
            "caveatOptions": {
                "true": "True",
                "false": "False"
            },
            formData: {}
        };
    },

    componentDidMount: function () {
        var that = this;
        $.ajax(this.props.dataURL, {
            dataType: 'json',
            xhr: function () {
                var xhr = new window.XMLHttpRequest();
                xhr.addEventListener("progress", function (evt) {
                    that.setState({
                        'loadedData': evt.loaded
                    });
                });
                return xhr;
            },
            success: function (data) {
                that.setState({
                    'Data': data,
                    'isLoaded': true
                });
            },
            error: function (data, error_code, error_msg) {
                that.setState({
                    'error': 'An error occurred when loading the form!'
                });
            }
        });
    },

    setFormData: function (formElement, value) {
        var formData = this.state.formData;
        formData[formElement] = value;

        this.setState({
            formData: formData
        });
    },

    onSubmit: function (e) {
        e.preventDefault();
    },

    render: function () {

        if (!this.state.isLoaded) {
            if (this.state.error != undefined) {
                return React.createElement(
                    "div",
                    { className: "alert alert-danger text-center" },
                    React.createElement(
                        "strong",
                        null,
                        this.state.error
                    )
                );
            }

            return React.createElement(
                "button",
                { className: "btn-info has-spinner" },
                "Loading",
                React.createElement("span", { className: "glyphicon glyphicon-refresh glyphicon-refresh-animate" })
            );
        }

        return React.createElement(
            FormElement,
            { name: "candidateInfo", onSubmit: this.handleSubmit, ref: "form", "class": "col-md-6" },
            React.createElement(HelpTextElement, {
                label: "PSCID",
                text: this.state.Data.pscid
            }),
            React.createElement(HelpTextElement, {
                label: "DCCID",
                text: this.state.Data.candID
            }),
            React.createElement(SelectElement, {
                label: "Caveat Emptor flag for Candidate",
                name: "flagged_caveatemptor",
                options: this.state.caveatOptions,
                onUserInput: this.setFormData,
                ref: "flagged_caveatemptor"
            }),
            React.createElement(SelectElement, {
                label: "Reason for Caveat Emptor flag",
                name: "flagged_reason",
                options: this.state.Data.caveatOptions,
                onUserInput: this.setFormData,
                ref: "flagged_reason"
            }),
            React.createElement(TextareaElement, {
                label: "If Other, please specify",
                name: "flagged_other",
                onUserInput: this.setFormData,
                ref: "flagged_other"
            }),
            React.createElement(ButtonElement, {
                label: "Update"
            })
        );
    },

    /**
     * Handles form submission
     * @param e
     */
    handleSubmit: function (e) {
        e.preventDefault();

        var myFormData = this.state.formData;
        var formRefs = this.refs;

        // Set form data and upload the media file
        var self = this;
        var formData = new FormData();
        for (var key in myFormData) {
            if (myFormData[key] != "") {
                formData.append(key, myFormData[key]);
            }
        }

        $.ajax({
            type: 'POST',
            url: self.props.action,
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            success: function (data) {
                self.setState({
                    uploadResult: "success",
                    formData: {} // reset form data after successful file upload
                });

                // Iterates through child components and resets state
                // to initial state in order to clear the form
                Object.keys(formRefs).map(function (ref) {
                    if (formRefs[ref].state && formRefs[ref].state.value) {
                        formRefs[ref].state.value = "";
                    }
                });
                // rerender components
                self.forceUpdate();
            },
            error: function (err) {
                var errorMessage = JSON.parse(err.responseText).message;
                self.setState({
                    uploadResult: "error",
                    errorMessage: errorMessage
                });
            }

        });
    }
});

RCandidateInfo = React.createFactory(CandidateInfo);