const moment1 = require('moment');

module.exports = {
    methods: {
        /**
         * Translate the given key.
         */
        __(key, replace = {}) {
            let translation = this.$page.props.language[key]
                ? this.$page.props.language[key]
                : key;

            Object.keys(replace).forEach(function (key) {
                translation = translation.replace(':' + key, replace[key]);
            });

            return translation;
        },
        formatDate(date, dateFormat = 'DD-MM-YYYY') {
            if (!date) return null;
            const momentDate = moment1(date);
            
            
            return momentDate.format(dateFormat);
          },
          
          formatTime(date, use24HourFormat = false) {
            if (!date) return null;
            const timeFormat = use24HourFormat ? 'HH:mm' : 'hh:mm A';
            return moment1(date).format(timeFormat);
          },
        formatDateTime(date, dateFormat = 'DD-MM-YYYY', use24HourFormat = false) {
            if (!date) return null; 
            const formattedDate = this.formatDate(date, dateFormat);
            const formattedTime = this.formatTime(date, use24HourFormat);
            
            return `${formattedDate} ${formattedTime}`;
        }
    },
};