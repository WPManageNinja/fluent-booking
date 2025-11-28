### WPFluent - WordPress Plugin development made easy.

### Installations

Requirements
- PHP 7.4+
- Node 14.21+
- NPM 8.19+

Installation
- `npm install`
- CRM Contact App: `cd resources/fluentCrmContact && npm install`
- Form Editor App: `cd resources/FluentFormEditor && npm install`

Running for dev
- Main App: `npx mix watch`
- CRM Contact App: `cd resources/fluentCrmContact && npx mix watch`
- Form Editor App: `cd resources/FluentFormEditor && npx mix watch`
- Frontend Development: `npx mix --mix-config=public-webpack.mix.js watch`

Running for production
- Main App: `npx mix --production`
- CRM Contact App: `cd resources/fluentCrmContact && npx mix --production`
- Form Editor App: `cd resources/FluentFormEditor && npx mix --production`
- Frontend Development: `npx mix --mix-config=public-webpack.mix.js --production`

### Production Build
- Free Build: `sh build.sh --node-build`
- Pro Build: `sh build.sh --node-build --with_pro`
- It will create a plugin folder(free/pro) in the /builds folder

### Build Files
- If you are just looking for already built files then go to 
[Releases](https://github.com/WPManageNinja/fluent-booking/releases) and download the latest release
as per your need.