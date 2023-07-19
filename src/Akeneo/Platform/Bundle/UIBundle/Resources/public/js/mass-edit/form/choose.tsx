import React from 'react';
import ReactDOM from 'react-dom';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import BaseView from "../../view/base";
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {ChooseApp} from "./ChooseApp";

const __ = require('oro/translator');
const analytics = require('pim/analytics');

class Choose extends BaseView {
  private config: any;

  initialize(config: any): void {
    this.config = config.config;
    BaseView.prototype.initialize.apply(this, arguments);
  }

  public render() {
    const operations = this.getParent().getOperations();
    const currentOperationCode = this.getParent().getCurrentOperation();
    const update = this.updateOperation.bind(this);

    ReactDOM.render(
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <ChooseApp
            operations={operations}
            selectedOperationCode={currentOperationCode}
            onChange={update}
          />
        </ThemeProvider>
      </DependenciesProvider>,
      this.el
    );

    return this;
  }

  public remove() {
    ReactDOM.unmountComponentAtNode(this.el);

    return super.remove();
  }

  private updateOperation(code: string) {
    this.getParent().setCurrentOperation(code);

    analytics.appcuesTrack('grid:mass-edit:item-chosen', {
      code: code,
    });
  }

  protected getLabel() {
    const itemsCount = this.getFormData().itemsCount;

    return __(this.config.title, {itemsCount}, itemsCount);
  }

  /**
   * Returns the title of the operation
   *
   * @returns {string}
   */
  protected getTitle() {
    return __(this.config.title);
  }

  /**
   * Returns the label with the count of impacted elements
   *
   * @returns {String}
   */
  protected getLabelCount() {
    const itemsCount = this.getFormData().itemsCount;

    return __(this.config.labelCount, {itemsCount}, itemsCount);
  }

  /**
   * {@inheritdoc}
   */
  protected getDescription() {
    return '';
  }

  /**
   * {@inheritdoc}
   */
  protected getIllustrationClass() {
    return '';
  }
};

export = Choose;
