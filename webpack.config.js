/**
 * ---------------------------------------------------------------------
 * ITSM-NG
 * Copyright (C) 2022 ITSM-NG and contributors.
 *
 * https://www.itsm-ng.org
 *
 * based on GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2003-2014 by the INDEPNET Development Team.
 *
 * ---------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of ITSM-NG.
 *
 * ITSM-NG is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * ITSM-NG is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with ITSM-NG. If not, see <http://www.gnu.org/licenses/>.
 * ---------------------------------------------------------------------
 */

const { CleanWebpackPlugin } = require('clean-webpack-plugin');
const CopyWebpackPlugin = require('copy-webpack-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');

const glob = require('glob');
const path = require('path');

const libOutputPath = 'public/lib';

/*
 * GLPI core files build configuration.
 */
const glpiConfig = {
  entry: {
    glpi: path.resolve(__dirname, 'js/main.js'),
    displaypreferences: path.resolve(__dirname, 'js/displaypreferences.js'),
    table: path.resolve(__dirname, 'js/table.js'),
  },
  output: {
    filename: '[name].js',
    path: path.resolve(__dirname, 'public/build'),
  },
  mode: 'none',
  devtool: 'source-map',
  stats: {
    all: false,
    errors: true,
    errorDetails: true,
    warnings: true,
    entrypoints: true,
    timings: true,
  },
  module: {
    rules: [
      {
        test: /\.js$/,
        exclude: /node_modules/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: ['@babel/preset-env'],
            plugins: ['babel-plugin-htm']
          },
        },
      },
    ],
  },
};

/*
 * External libraries files build configuration.
 */
const libsConfig = {
  entry: () => {
    const entries = {};
    const files = glob.sync(path.resolve(__dirname, 'lib/bundles') + '/!(*.min).js');
    files.forEach(file => {
      entries[path.basename(file, '.js')] = file;
    });
    return entries;
  },
  output: {
    filename: '[name].js',
    path: path.resolve(__dirname, libOutputPath),
  },
  module: {
    rules: [
      {
        test: /\.js$/,
        include: [
          path.resolve(__dirname, 'node_modules/@fullcalendar'),
          path.resolve(__dirname, 'node_modules/codemirror'),
          path.resolve(__dirname, 'node_modules/cystoscape'),
          path.resolve(__dirname, 'node_modules/cytoscape-context-menus'),
          path.resolve(__dirname, 'node_modules/gridstack'),
          path.resolve(__dirname, 'node_modules/jquery-migrate'),
          path.resolve(__dirname, 'node_modules/jstree'),
          path.resolve(__dirname, 'node_modules/photoswipe'),
          path.resolve(__dirname, 'node_modules/rrule'),
          path.resolve(__dirname, 'vendor/blueimp/jquery-file-upload'),
        ],
        use: ['script-loader', 'strip-sourcemap-loader'],
      },
      {
        test: /\.js$/,
        exclude: /node_modules/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: ['@babel/preset-env'],
          },
        },
      },
      {
        test: /\.css$/i,
        use: [MiniCssExtractPlugin.loader, 'css-loader'],
      },
      {
        test: /\.(gif|png|jpe?g|eot|ttf|svg|woff2?)$/,
        use: {
          loader: 'file-loader',
          options: {
            name: (filename) => {
              const sanitizedPath = path.relative(__dirname, filename)
                .replace(/[^\\/\w-.]/g, '')
                .split(path.sep)
                .filter((part, index) => part && index !== 0)
                .join('/');
              return sanitizedPath;
            },
          },
        },
      },
    ],
  },
  plugins: [
    new CleanWebpackPlugin(),
    new MiniCssExtractPlugin({ filename: '[name].css' }),
  ],
  resolve: {
    mainFields: ['main'],
    fallback: {
      tty: require.resolve('tty-browserify'),
      stream: require.resolve('stream-browserify'),
      buffer: require.resolve('buffer/'),
      os: require.resolve('os-browserify/browser'),
    },
  },
  mode: 'none',
  devtool: 'source-map',
  stats: {
    all: false,
    errors: true,
    errorDetails: true,
    warnings: true,
    entrypoints: true,
    timings: true,
  },
};

const libs = {
  '@fullcalendar': [{ context: 'core', from: 'locales/*.js' }],
  flatpickr: [
    { context: 'dist', from: 'l10n/*.js' },
    { context: 'dist', from: 'themes/*.css' },
  ],
  'jquery-ui': [{ context: 'ui', from: 'i18n/*.js' }],
  select2: [{ context: 'dist', from: 'js/i18n/*.js' }],
  'tinymce-i18n': [{ from: 'langs/*.js' }],
};

const generateCopyPatterns = (libPackage, packageName) => {
  const to = `${libOutputPath}/${packageName.replace(/^@/, '')}`;
  return libPackage.map(entry => ({
    context: path.resolve(__dirname, `node_modules/${packageName}`, entry.context || ''),
    from: entry.from,
    to: path.resolve(__dirname, to),
    toType: 'dir',
    ...(entry.ignore && { ignore: entry.ignore }),
  }));
};

Object.entries(libs).forEach(([packageName, libPackage]) => {
  const copyPatterns = generateCopyPatterns(libPackage, packageName);
  libsConfig.plugins.push(new CopyWebpackPlugin({ patterns: copyPatterns }));
});

module.exports = [glpiConfig, libsConfig];
